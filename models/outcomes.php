<?php

class Outcomes {
  function unprocessed($db, $todo) {
    $keywords_todo = 0;
    $concepts_todo = 0;
    $taxonomy_todo = 0;
    $entities_todo = 0;

    if (in_array("keywords", $todo)) {
      $result = $db->query("SELECT count(*) AS count FROM Outcomes WHERE keywords_done = false");
    
      if ($result) {
        $keywords_todo = $result->fetch_array()["count"] * 1;
      }
    }

    if (in_array("concepts", $todo)) {
      $result = $db->query("SELECT count(*) AS count FROM Outcomes WHERE concepts_done = false");
    
      if ($result) {
        $concepts_todo = $result->fetch_array()["count"] * 1;
      }
    }

    if (in_array("taxonomy", $todo)) {
      $result = $db->query("SELECT count(*) AS count FROM Outcomes WHERE taxonomy_done = false");
    
      if ($result) {
        $taxonomy_todo = $result->fetch_array()["count"] * 1;
      }
    }

    if (in_array("entities", $todo)) {
      $result = $db->query("SELECT count(*) AS count FROM Outcomes WHERE entities_done = false");
    
      if ($result) {
        $entities_todo = $result->fetch_array()["count"] * 1;
      }
    }

    return array("keywords" => $keywords_todo, "concepts" => $concepts_todo, "taxonomy" => $taxonomy_todo, "entities" => $entities_todo);
  }

  function next($db, $todo) {
    $sql = "SELECT Outcomes.id as outcome_id, Articles.id as article_id, Outcomes.* FROM Outcomes JOIN Articles ON Outcomes.article = Articles.id WHERE Articles.abstract != '' AND ( ";

    $where_list = array();

    if (in_array("keywords", $todo)) {
      array_push($where_list, "keywords_done = false");
    }

    if (in_array("concepts", $todo)) {
      array_push($where_list, "concepts_done = false");
    }

    if (in_array("taxonomy", $todo)) {
      array_push($where_list, "taxonomy_done = false");
    }

    if (in_array("entities", $todo)) {
      array_push($where_list, "entities_done = false");
    }

    $sql .= implode(" OR ", $where_list);

    $sql .= " ) ORDER BY RAND() LIMIT 1";
    
    $result = $db->query($sql);
    
    if ($result) {
      $result_array = $result->fetch_array();
      
      $done_array = array();

      if ($result_array["keywords_done"] == true) {
        array_push($done_array, "keywords");
      }

      if ($result_array["concepts_done"] == true) {
        array_push($done_array, "concepts");
      }

      if ($result_array["taxonomy_done"] == true) {
        array_push($done_array, "taxonomy");
      }

      if ($result_array["entities_done"] == true) {
        array_push($done_array, "entities");
      }

      $result_array["done"] = $done_array;

      return $result_array;
    }

    return false;
  }

  function update($db, $outcome_id, $done) {
    $sql = "UPDATE Outcomes SET ";

    $update_list = array();

    if (in_array("keywords", $done)) {
      array_push($update_list, "keywords_done = true");
    }

    if (in_array("concepts", $done)) {
      array_push($update_list, "concepts_done = true");
    }

    if (in_array("taxonomy", $done)) {
      array_push($update_list, "taxonomy_done = true");
    }

    if (in_array("entities", $done)) {
      array_push($update_list, "entities_done = true");
    }

    $sql .= implode(",", $update_list);

    $sql .= " WHERE id = $outcome_id";

    $result = $db->query($sql);
  }

  function insert($db, $article_id) {
    $result = $db->query("INSERT INTO Outcomes (article) VALUES ('$article_id')");

    if ($result) {
      return $db->connection->insert_id;
    }

    return false;
  }

  // Truncate the outcomes table
  function clear($db) {
    // Clear table
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    $db->query("TRUNCATE TABLE Outcomes");
    $db->query("SET FOREIGN_KEY_CHECKS = 1");
  }
}