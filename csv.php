<?php
  header("Content-disposition: attachment; filename=articles.csv");
  header("Content-Type: text/csv");

  require_once("config.php");
  require_once("database.php");

  $db = new Connector();

  $db->connect($config);

  // Get articles from DB
  $articles = $db->query("SELECT * FROM Articles");

  $output = array();

  while ($article = $articles->fetch_array()) {
    // Filter articles with an empty abstract
    if ($article["abstract"] != "") {
      // Filter any non characters, retain a-z, - and spaces
      // Merge title and abstract into one CSV row
      $text = preg_replace("/[^a-zA-Z'\-\ ]/", "", $article["title"] . " " . $article["abstract"]);

      array_push($output, $text);
    }
  }

  // Echo output as CSV
  echo implode(",\n", $output) . "\n";