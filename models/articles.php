<?php

class Articles {
  function find_by_id($db, $id) {
    $result = $db->query("SELECT * FROM Articles WHERE id = $id");

    if ($result) {
      return $result->fetch_array();
    } 

    return false;
  }

  function find($db, $title, $journal_id = "") {
    $title_fixed = addslashes($title);

    $sql = "SELECT * FROM Articles WHERE title LIKE '%$title_fixed%'";

    if (strlen($journal_id) > 0) {
      $sql .= " OR journal = '$journal_id'";
    }

    $result = $db->query($sql);

    if ($result) {
      return $result->fetch_array();
    } 

    return false;
  }

  function insert($db, $article) {
    require_once("journals.php");
    require_once("keywords.php");
    require_once("outcomes.php");

    $journal_model = new Journals();
    $keyword_model = new Keywords();
    $outcome_model = new Outcomes();

    // Insert journal
    $journal_id = $journal_model->insert($db, $article["journal_title"], $article["issn"], $article["journal_iso"]);

    // Insert article
    $title_fixed = addslashes($article["title"]);
    $abstract_fixed = addslashes($article["abstract"]);

    $sql = "INSERT INTO Articles (title, abstract, journal) VALUES ('$title_fixed', '$abstract_fixed', '$journal_id')";
    
    if ($result = $db->query($sql)) {
      $article_id = $db->connection->insert_id;

      // Insert keywords
      foreach ($article["keywords"] as $keyword) {
        $keyword_model->insert($db, $keyword, $article_id);
      }

      // Init the outcome couple table
      $outcome_model->insert($db, $article_id);

      return $article_id;
    }

    return false;
  }

  function clear($db) {
    // Clear table
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    $db->query("TRUNCATE TABLE Articles");
    $db->query("SET FOREIGN_KEY_CHECKS = 1");
  }

  function is_filled($db) {
    $result = $db->query("SELECT id FROM Articles");

    if ($result && $result->num_rows > 0) {
      return true;
    }

    return false;
  }
}

?>