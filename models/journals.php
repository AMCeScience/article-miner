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

    $sql = "SELECT * FROM Journals WHERE title LIKE '%$title_fixed%'";

    if (strlen($iso) > 0) {
      $sql .= " OR iso LIKE '%$iso%'";
    }

    if (strlen($issn) > 0) {
      $sql .= " OR issn LIKE '%$issn%'";
    }

    $result = $db->query($sql);

    if ($result) {
      return $result->fetch_array();
    } 

    return false;
  }

  // Insert a journal
  function insert($db, $title, $iso = "", $issn = "") {
    require_once("journal_definitions.php");

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
    
    if ($result = $db->query("INSERT INTO Journals (title, iso, issn) VALUES ('$title_fixed', '$iso', '$issn')")) {
      $journal_id = $db->connection->insert_id;

      include("../config.php");
      
      // Check if journal list is set in config
      if ($config["journal_list_run"] === true) {
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
    $query = "SELECT count(a.id) AS count, j.id, j.title, j.issn
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
    
    $db->query("UPDATE Journals SET title = '$title_fixed', iso = '$iso', issn = '$issn' WHERE id = $id");
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