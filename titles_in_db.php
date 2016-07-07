<?php

$time_start = microtime(true);

require_once("config.php");
require_once("database.php");

$db = new Connector();

$db->connect($config);

require_once("models/articles.php");

$article_model = new Articles();

$contents = file('files/titles.txt');

foreach($contents as $line) {
  $found_articles = $article_model->find_by_stripped_title($db, $line);

  if (!$found_articles) {
    echo $line . '<br/>';

    continue;
  }

  $count = 0;

  while ($row = $found_articles->fetch_assoc()) {
    $count++;
  }

  echo $count . '<br/>';
}