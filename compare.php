<?php
  $time_start = microtime(true);

  require_once("config.php");
  require_once("database.php");

  $db = new Connector();

  $db->connect($config);

  require_once("models/articles.php");

  $article_model = new Articles();

  $titles_and_dois = $article_model->get_titles_and_dois($db);

  $titles_clean = array();
  $dois_clean = array();

  if ($titles_and_dois) {
    while ($row = $titles_and_dois->fetch_assoc()) {
      if (($row['title_count'] * 1) > 1) {
        $titles_clean[$row['id']] = $row['title_stripped'];
      }

      if (($row['doi_count'] * 1) > 1) {
        $dois_clean[$row['id']] = $row['doi'];
      }
    }
  }

  $unique_titles = array_unique($titles_clean);
  $unique_dois = array_unique($dois_clean);

  $ids = array();

  foreach($unique_titles as $id => $title) {
    array_push($ids, $id);
  }

  foreach($unique_dois as $id => $doi) {
    array_push($ids, $id);
  }

  $ids = array_unique($ids);

  if (count($ids) > 1) {
    echo "Removing " . count($ids) . " duplicates.<br/>";

    $article_model->delete($db, $ids);
  }

  echo "Time elapsed: " . (microtime(true) - $time_start) . "s <br/>";

  echo "<a href='/index.php'>Back to index page</a>";