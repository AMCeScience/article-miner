<?php

class Outcomes {
  function last($db) {
    $result = $db->query("SELECT * FROM Outcomes ORDER BY id DESC LIMIT 1");
    
    if ($result) {
      return $result->fetch_array();
    }

    return array("article" => 1);
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