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

  function statistics($db) {
    $result_concepts = $db->query("SELECT text, count(*) as count FROM Alchemy_concepts group by text order by count desc limit 30");
    $result_taxonomy = $db->query("SELECT label, count(*) as count FROM Alchemy_taxonomy where confident != 'no' group by label order by count desc limit 30");
    $result_entities = $db->query("SELECT text, type, count(*) as count FROM Alchemy_entities group by text order by count desc limit 30");
    $result_keywords = $db->query("SELECT text, count(*) as count FROM Alchemy_keywords group by text order by count desc limit 30");

    return array("concepts" => $result_concepts, "taxonomy" => $result_taxonomy, "entities" => $result_entities, "keywords" => $result_keywords);
  }
}

?>