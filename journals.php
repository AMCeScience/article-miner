<?php

class Journals {
  function find($db, $title) {

  }

  function insert($db, $title, $issn, $iso) {
    require_once("journal_definitions.php");

    // Insert journal

    // Find definition
    $definitions = new Journal_definitions();
    $journal_definition = $definitions->find($db, $title, $issn, $iso);

    $journal_definition_id = '';

    if ($journal_definition) {
      $journal_definition_id = $journal_definition['id'];

      // Insert couple table
    }
  }
}

?>