<?php

class Journal_list {
  function run($config, $db) {
    if ($config["journal_list_reinit"] || !$this->is_filled($db)) {
      echo "Reinitializing the journal list... ";
      $this->parse($config, $db);
      echo "done.<br/>";
    } else {
      echo "Skipping journal list initialisation.<br/>";
    }
  }

  function parse($config, $db) {
    // Open CSV file
    $csv_file = fopen($config["journal_list_dir"], "r");

    // Clear table
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    $db->query("TRUNCATE TABLE Journal_definitions");
    $db->query("SET FOREIGN_KEY_CHECKS = 1");

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

  function is_filled($db) {
    $result = $db->query("SELECT id FROM Journal_definitions");

    if ($result && $result->num_rows > 0) {
      return true;
    }

    return false;
  }
}

?>