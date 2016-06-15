<?php

// Article Miner, a document parser for Pubmed, Pubmed Central, Ovid, Scopus, and Web of Science
// Copyright (C) 2016 Allard van Altena

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.

// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

class AlchemyOutcomes {
  function insert_keyword($db, $keyword, $outcome_id) {
    $fixed = addslashes($keyword["text"]);

    $db->query("INSERT INTO Alchemy_keywords (text, relevance, outcome) VALUES ('$fixed', '{$keyword["relevance"]}', '$outcome_id')");
  }

  function insert_concept($db, $concept, $outcome_id) {
    $fixed = addslashes($concept["text"]);

    $db->query("INSERT INTO Alchemy_concepts (text, relevance, outcome) VALUES ('$fixed', '{$concept["relevance"]}', '$outcome_id')");
  }

  function insert_taxonomy($db, $taxonomy, $outcome_id) {
    $confident = "";

    if (isset($taxonomy["confident"])) {
      $confident = $taxonomy["confident"];
    }

    $fixed = addslashes($taxonomy["label"]);

    $db->query("INSERT INTO Alchemy_taxonomy (label, score, confident, outcome) VALUES ('$fixed', '{$taxonomy["score"]}', '$confident', '$outcome_id')");
  }

  function insert_entity($db, $entity, $outcome_id) {
    $fixed = addslashes($entity["type"]);
    $text_fixed = addslashes($entity["text"]);

    $db->query("INSERT INTO Alchemy_entities (type, relevance, count, text, outcome) VALUES ('$fixed', '{$entity["relevance"]}', '{$entity["count"]}', '$text_fixed', '$outcome_id')");
  }

  // Get top 30 results for each of the Alchemy outcomes
  function statistics($db) {
    $result_concepts = $db->query("SELECT text, count(*) as count FROM Alchemy_concepts group by text order by count desc limit 30");
    $result_taxonomy = $db->query("SELECT label, count(*) as count FROM Alchemy_taxonomy where confident != 'no' group by label order by count desc limit 30");
    $result_entities = $db->query("SELECT text, type, count(*) as count FROM Alchemy_entities group by text order by count desc limit 30");
    $result_keywords = $db->query("SELECT text, count(*) as count FROM Alchemy_keywords group by text order by count desc limit 30");

    return array("concepts" => $result_concepts, "taxonomy" => $result_taxonomy, "entities" => $result_entities, "keywords" => $result_keywords);
  }

  // Breaks taxonomy into its components (i.e. split on '/')
  // Groups components based on taxonomy tree, for full taxonomy category list see documentation: http://www.alchemyapi.com/api/taxonomy
  function taxonomy_outcomes_by_level($db) {
    return $db->query("SELECT *, AVG(score), COUNT(id) AS count, ROUND((LENGTH(label) - LENGTH(REPLACE(label, '/', '')))) AS level
      FROM Alchemy_taxonomy
      GROUP BY label
      ORDER BY count DESC");
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