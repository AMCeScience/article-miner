<?php

class Outcomes {
  function next($db, $todo) {
    $sql = "SELECT * FROM Outcomes JOIN Articles ON Outcomes.article = Articles.id WHERE Articles.abstract != '' AND Outcomes.article = 1591 AND ( ";

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
}

?>