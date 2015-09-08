<?php

class AlchemyOutcomes {
  function insert_keyword($db, $keyword, $outcome_id) {
    $db->query("INSERT INTO Alchemy_keywords (text, relevance, outcome) VALUES ('{$keyword["text"]}', '{$keyword["relevance"]}', '$outcome_id')");
  }

  function insert_concept($db, $concept, $outcome_id) {
    $db->query("INSERT INTO Alchemy_concepts (text, relevance, outcome) VALUES ('{$concept["text"]}', '{$concept["relevance"]}', '$outcome_id')");
  }

  function insert_taxonomy($db, $taxonomy, $outcome_id) {
    $confident = "";

    if (isset($taxonomy["confident"])) {
      $confident = $taxonomy["confident"];
    }

    $db->query("INSERT INTO Alchemy_taxonomy (label, score, confident, outcome) VALUES ('{$taxonomy["label"]}', '{$taxonomy["score"]}', '$confident', '$outcome_id')");
  }

  function insert_entity($db, $entity, $outcome_id) {
    $db->query("INSERT INTO Alchemy_entities (type, relevance, count, text, outcome) VALUES ('{$entity["type"]}', '{$entity["relevance"]}', '{$entity["count"]}', '{$entity["text"]}', '$outcome_id')");
  }

  function clear($db) {
    // Clear table
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    $db->query("TRUNCATE TABLE Outcomes");
    $db->query("TRUNCATE TABLE Alchemy_keywords");
    $db->query("TRUNCATE TABLE Alchemy_concepts");
    $db->query("TRUNCATE TABLE Alchemy_taxonomy");
    $db->query("TRUNCATE TABLE Alchemy_entities");
    $db->query("SET FOREIGN_KEY_CHECKS = 1");
  }
}

?>