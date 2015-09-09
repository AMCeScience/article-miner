<?php

$time_start = microtime(true);

require_once("config.php");
require_once("database.php");

$db = new Connector();

$db->connect($config);

require_once("models/articles.php");
require_once("models/outcomes.php");
require_once("models/alchemy_outcomes.php");
require_once("models/scriptchecker.php");

$article_model = new Articles();
$outcome_model = new Outcomes();
$alchemy_outcome_model = new AlchemyOutcomes();
$alchemy_transactions_model = new AlchemyTransactions();

// Start Alchemy API
require_once("alchemyAPI/alchemyapi.php");

$alchemyapi = new AlchemyAPI($config["alchemy_key_dir"]);

$safety_catch = 0;
$article_count = 0;

$transactions = $alchemyapi->transactioninfo();

if ($transactions["status"] == "OK") {
  $config["alchemy_transactions"] = $transactions["dailyTransactionLimit"] * 1;

  $alchemy_transactions_model->insert($db, $transactions["consumedDailyTransactions"] * 1);

  if (($transactions["dailyTransactionLimit"] * 1) == ($transactions["consumedDailyTransactions"])) {
    echo "Already over limit, quitting...";

    exit;
  }
} else {
  echo "No response from API, quitting...";

  exit;
}

// Loop for the allowed amount of alchemy transactions
while ($alchemy_transactions_model->used_today($db) <= $config["alchemy_transactions"] - count($config["alchemy_collect"]) && $safety_catch < 5000) {
  $required_alchemy_items = $config["alchemy_collect"];

  // Get article id from the last inserted outcome
  $this_outcome = $outcome_model->next($db, $required_alchemy_items);
  
  if (!$this_outcome) {
    echo "No outcome was selected";
    
    exit;
  }

  // Find the next article to process
  $this_article = $article_model->find_by_id($db, $this_outcome["article"]);

  $items_todo = array_diff($required_alchemy_items, $this_outcome["done"]);

  if (count($items_todo) == 0) {
    // We are done here, move to next article
    continue;
  }

  // If article exists
  if ($this_article) {
    $options["extract"] = implode(",", $items_todo);

    // Get response
    $response = $alchemyapi->combined('text', $this_article["title"] . " " . $this_article["abstract"], $options);
    
    // $response = array(
    //   "status" => "OK", 
    //   "usage" => "By accessing AlchemyAPI or using information generated by AlchemyAPI, you are agreeing to be bound by the AlchemyAPI Terms of Use: http://www.alchemyapi.com/company/terms.html",
    //   "totalTransactions" => 4,
    //   "language" => "english",
    //   "keywords" => array(
    //     array("text" => "fancy iPhone", "relevance" => 0.983037),
    //     array("text" => "dumb Bob", "relevance" => 0.904227),
    //     array("text" => "beautiful Denver", "relevance" => 0.835704),
    //     array("text" => "Apple Store", "relevance" => 0.721868),
    //     array("text" => "Colorado", "relevance" => 0.38689)
    //   ),
    //   "concepts" => array(
    //     array("text" => "Apple Store", "relevance" => 0.932431),
    //     array("text" => "Apple Inc.", "relevance" => 0.891859)
    //   ),
    //   "entities" => array(
    //     array("type" => "FieldTerminology", "relevance" => 0.884345, "count" => 1, "text" => "Apple Store"),
    //     array("type" => "Technology", "relevance" => 0.728395, "count" => 1, "text" => "iPhone")
    //   ),
    //   "taxonomy" => array(
    //     array("label" => "/shopping/retail/online stores", "score" => 0.692631),
    //     array("confident" => "no", "label" => "/technology and computing/consumer electronics/telephones/mobile phones/smart phones", "score" => 0.212776),
    //     array("confident" => "no", "label" => "/style and fashion/clothing/pants", "score" => 0.14874)
    //   )
    // );
    
    // Got response
    if ($response["status"] == "OK") {
      if (isset($response["keywords"])) {
        // Insert keywords
        $keywords = $response["keywords"];

        foreach ($keywords as $keyword) {
          $alchemy_outcome_model->insert_keyword($db, $keyword, $this_outcome["id"]);
        }
      }

      if (isset($response["concepts"])) {
        // Insert concepts
        $concepts = $response["concepts"];

        foreach ($concepts as $concept) {
          $alchemy_outcome_model->insert_concept($db, $concept, $this_outcome["id"]);
        }
      }

      if (isset($response["entities"])) {
        // Insert entities
        $entities = $response["entities"];

        foreach ($entities as $entity) {
          $alchemy_outcome_model->insert_entity($db, $entity, $this_outcome["id"]);
        }
      }

      if (isset($response["taxonomy"])) {
        // Insert taxonomy
        $taxonomies = $response["taxonomy"];

        foreach ($taxonomies as $taxonomy) {
          $alchemy_outcome_model->insert_taxonomy($db, $taxonomy, $this_outcome["id"]);
        }
      }

      // Update outcome
      $outcome_model->update($db, $this_outcome["id"], $items_todo);

      // Update used transactions
      if (!isset($response["totalTransactions"])) {
        $response["totalTransactions"] = 1;
      }

      $alchemy_transactions_model->update_today($db, $response["totalTransactions"] * 1);
    } else {
      echo "Error in the taxonomy call: " . $response["statusInfo"] . "<br/>";
    }
  } else {
    echo "Error: no next article.<br/>";

    // Break out of the while loop
    $safety_catch = 5000;
  }
  
  $safety_catch++;
  $article_count++;
}

echo "Done<br/><br/>";

$transactions_after = $alchemyapi->transactioninfo();

echo "<span style=\"font-weight: bold; font-size: 18px;\">AlchemyAPI results:</span><br/>";
// echo "Used transactions: " . $transactions_after["consumedDailyTransactions"] . "<br/>";
echo "Allowed transactions: " . $transactions_after["dailyTransactionLimit"] . "<br/>";

$db_used = $alchemy_transactions_model->used_today($db);

echo "<span style=\"font-weight: bold; font-size: 18px;\">Database results:</span><br/>";
echo "Used transactions: " . $db_used . "<br/>";
echo "Articles ran: " . $article_count . "<br/>";
echo "Average transactions per article: " . $db_used / $article_count . "</br>";

echo "<br/>";

// echo "API and database agree: ";

// if ($transactions_after["consumedDailyTransactions"] == $db_used) {
//   echo "true<br/>";
// } else {
//   echo "false<br/>";
// }

echo "Time elapsed: " . (microtime(true) - $time_start) . "s";

?>