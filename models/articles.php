<?php

class Articles {
  function find_by_id($db, $id) {
    $result = $db->query("SELECT * FROM Articles WHERE id = $id");

    if ($result) {
      return $result->fetch_array();
    } 

    return false;
  }

  function get_titles_and_dois($db) {
    $result = $db->query("SELECT id, title_stripped, doi,
      (SELECT count(inner_article.id) FROM Articles AS inner_article WHERE inner_article.doi = outer_article.doi AND inner_article.doi != '' GROUP BY inner_article.doi) as doi_count,
      ((SELECT count(inner_article.id) FROM Articles AS inner_article WHERE inner_article.title_stripped = outer_article.title_stripped AND inner_article.title_stripped != '' GROUP BY inner_article.title_stripped)) as title_count
      FROM Articles AS outer_article
        WHERE (SELECT count(inner_article.id) FROM Articles AS inner_article WHERE inner_article.title_stripped = outer_article.title_stripped AND inner_article.title_stripped != '' GROUP BY inner_article.title_stripped) > 1
          OR (SELECT count(inner_article.id) FROM Articles AS inner_article WHERE inner_article.doi = outer_article.doi AND inner_article.doi != '' GROUP BY inner_article.doi) > 1
    ");

    return $result;
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

  function insert($db, $article, $database) {
    require_once("outcomes.php");

    // $journal_model = new Journals();

    // Insert journal
    // $journal_id = $journal_model->insert($db, $article["journal_title"], $article["journal_iso"], $article["journal_issn"]);
    $journal_id = 0;

    // Insert article
    $title_stripped = preg_replace("/[^a-z]/i", "", strtolower($article["title"]));
    $title_fixed = addslashes($article["title"]);
    $abstract_fixed = addslashes($article["abstract"]);

    $sql = "INSERT INTO Articles (title, title_stripped, abstract, journal, day, month, year) VALUES ('$title_fixed', '$title_stripped', '$abstract_fixed', '$journal_id', '{$article['day']}', '{$article['month']}', '{$article['year']}')";
    
    if ($result = $db->query($sql)) {
      $article_id = $db->connection->insert_id;

      $outcomes_model = new Outcomes();
      $outcomes_model->insert($db, $article_id);

      return $article_id;
    }

    return false;
  }

  function delete($db, $ids) {
    $ids = implode(",", $ids);

    $db->query("DELETE FROM Articles WHERE id IN ($ids)");
  }

  function clear($db) {
    // Clear table
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    $db->query("TRUNCATE TABLE Articles");
    $db->query("TRUNCATE TABLE Outcomes");
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