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

class Journal_list {
  function __construct($config, $db) {
    require_once("models/journal_definitions.php");

    $definitions_model = new Journal_definitions();

    if ($config["journal_list_reinit"] || !$definitions_model->is_filled($db)) {
      echo "Reinitializing the journal list... ";
      $this->parse($config, $db);
      echo "done.<br/>";
    } else {
      echo "Skipping journal list initialisation.<br/>";
    }
  }

  function parse($config, $db) {
    // Open CSV file
    if (($csv_file = @fopen($config["journal_list_dir"], "r")) === false) {
      echo "<br/> Error: CSV file not found at: " . $config["journal_list_dir"] . " ";

      return;
    }

    $data = array();

    // Loop CSV file to get all the journals
    while ($line = fgets($csv_file)) {
      // Split CSV line
      $parsed_line = str_getcsv($line, ";");

      // Skip header row
      if ($parsed_line[0] == 'Title20') {
        continue;
      }

      // If the last row has the same issn as this one, they are the same journal
      // Append the category and category type to the last row
      if (count($data) > 0 && $data[count($data) - 1]["issn"] == $parsed_line[3]) {
        array_push($data[count($data) - 1]["category"], $parsed_line[5]);
        array_push($data[count($data) - 1]["category_type"], $parsed_line[4]);
      } else {
        // Add a new row to
        array_push($data, array("title" => $parsed_line[2], "iso" => $parsed_line[1], "issn" => $parsed_line[3], "category" => array($parsed_line[5]), "category_type" => array($parsed_line[4])));
      }
    }

    // Insert SQL statement
    $sql = "INSERT INTO Journal_definitions (title, iso, issn, category, category_type) VALUES";

    // Merge all the lines into one array
    $lines = array();

    foreach ($data as $journal) {
      array_push($lines, "('" . addslashes($journal["title"]) . "','" . $journal["iso"] . "','" . $journal["issn"] . "','" . addslashes(implode($journal["category"], "; ")) . "','" . implode($journal["category_type"], "; ") . "')");
    }

    // Implode array
    $sql .= implode(",", $lines);

    // Insert
    $db->query($sql);
  }
}