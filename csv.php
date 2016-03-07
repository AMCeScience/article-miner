<?php
  header("Content-disposition: attachment; filename=articles.csv");
  header("Content-Type: text/csv");

  require_once("config.php");
  require_once("database.php");

  $db = new Connector();

  $db->connect($config);

  $articles = $db->query("SELECT * FROM Articles");

  $output = array();

  while ($article = $articles->fetch_array()) {
    if ($article["abstract"] != "") {
      $text = preg_replace("/[^a-zA-Z'\-\ ]/", "", $article["title"] . " " . $article["abstract"]);

      array_push($output, $text);
    }
  }

  echo implode(",\n", $output) . "\n";
?>