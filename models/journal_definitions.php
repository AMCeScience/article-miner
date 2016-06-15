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

class Journal_definitions {
  // Search for a journal definition by title
  // Optionally also searches by iso or issn
  function find($db, $title, $iso = "", $issn = "") {
    $title_fixed = addslashes($title);

    $sql = "SELECT * FROM Journal_definitions WHERE title LIKE '%$title_fixed%'";

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

  // Checks whether there are any rows in the journal definitions table
  function is_filled($db) {
    $result = $db->query("SELECT id FROM Journal_definitions");

    if ($result && $result->num_rows > 0) {
      return true;
    }

    return false;
  }

  // Truncate the journal definitions table
  function clear($db) {
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    $db->query("TRUNCATE TABLE Journal_definitions");
    $db->query("SET FOREIGN_KEY_CHECKS = 1");
  }
}