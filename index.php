<?php
  require_once("config.php");
  require_once("database.php");
  require_once("models/articles.php");
  require_once("models/outcomes.php");
  require_once("alchemyAPI/alchemyapi.php");

  $db = new Connector();

  $db->connect($config);

  $article_model = new Articles();
  $outcomes_model = new Outcomes();

  // Get some counts for display
  $article_count = $article_model->count($db);
  $abstract_count = $article_model->count_not_empty($db);
  $unprocessed = $outcomes_model->unprocessed($db, $config["alchemy_collect"]);  

  // Initialise Alchemy with key
  $alchemyapi = new AlchemyAPI($config["alchemy_key_dir"]);

  // Get key transaction status
  $transactions = $alchemyapi->transactioninfo();
?>

<html>
  <head>
    <title>Article parser</title>
  </head>
  <body style="padding: 0; margin: 0; background-color: #333;">
    <div style="padding: 25px; margin: 0 auto; width: 900px; background-color: white; height: 100%;">
      <p>
        <a href="init.php">Initialise the database</a>
      </p>
      <p>
        <a href="init.php?reinit=true">Re-initialise the database (removes all data)</a>
      </p>
      <p>
        <a href="compare.php">Remove duplicates from articles</a>
      </p>
      <p>
        <a href="analyse.php">Run AlchemyAPI</a>
      </p>
      <p>
        <a href="csv.php">Get articles CSV</a>
      </p>
      <p>
        Articles in database: <?php echo $article_count; ?> </br/>
        Articles with abstracts: <?php echo $abstract_count; ?> </br/>
        <h3>Alchemy Info</h3>
        Alchemy transactions used today: <?php echo $transactions["consumedDailyTransactions"]; ?> <br/>
        Alchemy key transactions limit: <?php echo $transactions["dailyTransactionLimit"]; ?><br/>
        <br/>
        To run:<br/>
        <?php foreach($unprocessed as $type => $value) {
          echo ucfirst($type) . ": " . $value . "<br/>";
        } ?>
        <br/>
        Total transactions necessary to finish: <?php echo array_sum($unprocessed); ?>
      </p>
    </div>
  </body>
</html>