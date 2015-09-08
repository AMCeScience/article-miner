<?php

class Journals {
  function find($db, $title, $issn, $iso) {
    $title_fixed = addslashes($title);

    $sql = "SELECT * FROM Journals WHERE title LIKE '%$title_fixed%'";

    if (strlen($issn) == 9) {
      $sql .= " OR issn = '$issn'";
    }

    if (strlen($iso) > 0) {
      $sql .= " OR iso LIKE '%$iso%'";
    }

    $result = $db->query($sql);

    if ($result) {
      return $result->fetch_array();
    } 

    return false;
  }

  function insert($db, $title, $issn = "", $iso = "") {
    require_once("journal_definitions.php");

    // Find existing journal
    $existing_journal = $this->find($db, $title, $issn, $iso);

    if ($existing_journal) {
      return $existing_journal["id"];
    }

    // Insert journal
    $title_fixed = addslashes($title);

    $sql = "INSERT INTO Journals (title, issn, iso) VALUES ('$title_fixed', '$issn', '$iso')";
    
    if ($result = $db->query($sql)) {
      $journal_id = $db->connection->insert_id;

      // Find definition
      $definitions = new Journal_definitions();
      $journal_definition = $definitions->find($db, $title, $issn, $iso);

      $journal_definition_id = '';

      if ($journal_definition) {
        $journal_definition_id = $journal_definition["id"];

        // Insert couple table
        $db->query("INSERT INTO Journals_to_definitions (journal, definition) VALUES ('$journal_id', '$journal_definition_id')");
      }

      return $journal_id;
    }

    return false;
  }

  function clear($db) {
    // Clear table
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    $db->query("TRUNCATE TABLE Journals");
    $db->query("TRUNCATE TABLE Journals_to_definitions");
    $db->query("SET FOREIGN_KEY_CHECKS = 1");
  }
}

?>