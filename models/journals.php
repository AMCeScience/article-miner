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

class Journals {
  // Search for a journal by title
  // Optionally also searches by iso or issn
  function find($db, $title, $iso = "", $issn = "") {
    $title_fixed = addslashes($title);

    $sql = "SELECT * FROM Journals WHERE title = '$title_fixed'";

    if (strlen($iso) > 0) {
      $iso_stripped = preg_replace("/[^a-z]/i", "", strtolower($iso));

      $sql .= " OR iso = '$iso'";
      $sql .= " OR iso_stripped = '$iso_stripped'";
    }

    if (strlen($issn) > 0) {
      $sql .= " OR issn = '$issn'";
    }
      

    $result = $db->query($sql);

    if ($result) {
      return $result->fetch_array();
    } 

    return false;
  }

  function find_distinct($db, $search_db = "") {
    $query = "SELECT DISTINCT(journal) FROM Articles";

    if ($search_db !== "") {
      $query .= " WHERE search_db = '{$search_db}'";
    }

    return $db->query($query);
  }

  function find_excluded_ids($db, $exclusion_list) {
    $issn = implode("','", array_keys($exclusion_list));
    $titles = implode("','", $exclusion_list);

    $where = array();

    if (count($issn) > 0) {
      $where[] = "issn IN ('{$issn}')";
    }

    if (count($titles) > 0) {
      $where[] = "title IN ('{$titles}')";
    }

    if (count($where) > 0) {
      $where = "WHERE " . implode(' OR ', $where);
    }

    $sql = "SELECT id FROM Journals {$where}";
    
    $result = $db->query($sql);

    $journal_ids = array();
    
    if ($result === false) {
        return $journal_ids;
    }
    
    while ($data = $result->fetch_array()) {
        $journal_ids[] = $data['id'];
    }
    
    return $journal_ids;
  }

  // Insert a journal
  function insert($db, $title, $iso = "", $issn = "", $journal_list = false) {
    require_once("journal_definitions.php");

    $iso_stripped = preg_replace("/[^a-z]/i", "", strtolower($iso));

    // Find existing journal
    $existing_journal = $this->find($db, $title, $iso, $issn);

    if ($existing_journal && $existing_journal["issn"] == '' && $issn != '') {
      $this->update($db, $existing_journal["id"], $existing_journal["title"], $existing_journal["iso"], $issn);
    }
    
    if ($existing_journal && $existing_journal["iso"] == '' && $iso != '') {
      $this->update($db, $existing_journal["id"], $existing_journal["title"], $iso, $existing_journal["issn"]);
    }

    // If journal already exists in DB, return the id
    if ($existing_journal) {
      return $existing_journal["id"];
    }

    // Insert journal
    $title_fixed = addslashes($title);
    
    if ($result = $db->query("INSERT INTO Journals (title, iso, issn, iso_stripped) VALUES ('$title_fixed', '$iso', '$issn', '$iso_stripped')")) {
      $journal_id = $db->connection->insert_id;
      
      // Check if journal list is set in config
      if ($journal_list === true) {
        // Find definition
        $definitions = new Journal_definitions();
        $journal_definition = $definitions->find($db, $title, $iso, $issn);

        // If definition was found insert a link back to the inserted journal ($journal_id)
        if ($journal_definition) {
          // Insert into couple table
          $db->query("INSERT INTO Journals_to_definitions (journal, definition) VALUES ('$journal_id', '{$journal_definition['id']}')");
        }
      }

      return $journal_id;
    }

    return false;
  }

  function get_relevant_journals($db) {
    // $query = "SELECT count(a.id) AS count, j.id, j.iso, j.title, j.issn
    //   FROM articles a
    //   JOIN journals j ON a.journal = j.id
    //   WHERE a.abstract != ''
    //     AND j.id IN (77,85,92,246,381,389,412,424,518,549,567,590,593,643,813)
    //   GROUP BY a.journal
    //   ORDER BY count DESC";
    $query = "SELECT count(a.id) AS count, j.id, j.iso, j.title, j.issn
      FROM articles a
      JOIN journals j ON a.journal = j.id
      WHERE a.abstract != ''
      GROUP BY a.journal
      #having count(a.id) > 9
      ORDER BY count DESC";

    if ($result = $db->query($query)) {
      return $result;
    }

    return false;
  }

  function get_pubmed_fetched_journals($db) {
    $done_sql = "SELECT DISTINCT(journal) FROM Articles WHERE search_db = 'robot'";

    $done_array = array();

    if ($done_result = $db->query($done_sql)) {
      while ($result = $done_result->fetch_array()) {
        $done_array[] = $result['journal'];
      }
    }

    return $done_array;
  }

  function get_pubmed_journals($db) {
    $query = "SELECT DISTINCT(journal_id) FROM Pubmed_articles";

    if ($result = $db->query($query)) {
      return $result;
    }

    return false;
  }

  function update($db, $id, $title, $iso = "", $issn = "") {
    // Insert journal
    $title_fixed = addslashes($title);

    $iso_stripped = preg_replace("/[^a-z]/i", "", strtolower($iso));
    
    $query = "UPDATE Journals SET title = '$title_fixed'";

    if (strlen($iso) > 0) {
      $query .= ", iso = '$iso'";
      $query .= ", iso_stripped = '$iso_stripped'";
    }

    if (strlen($issn) > 0) {
      $query .= ", issn = '$issn'";
    }

    $query .= " WHERE id = $id";

    $db->query($query);
  }

  function delete($db, $id) {
    $db->query("DELETE FROM Journals WHERE id = {$id}");

    return $db->connection->affected_rows;
  }

  // Truncate the journals table
  function clear($db) {
    // Clear table
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    $db->query("TRUNCATE TABLE Journals");
    $db->query("TRUNCATE TABLE Journals_to_definitions");
    $db->query("SET FOREIGN_KEY_CHECKS = 1");
  }
}