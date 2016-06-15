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

class ScriptChecker {
  // Checke whether a script identified by 'name' has already ran
  function has_ran($db, $name) {
    $result = $this->find($db, $name);

    if ($result) {
      return (boolean) $result["ran"];
    }

    return false;
  }

  // Get the script ran entry for a script identified by 'name'
  function find($db, $name) {
    $sql = "SELECT * FROM Scripts_ran WHERE name = '$name'";

    $result = $db->query($sql);

    if ($result) {
      return $result->fetch_array();
    }

    return false;
  }

  // Set the ran value for a script identified by 'name'
  function set($db, $name, $value = true) {
    $result = $this->find($db, $name);

    $value = (int) $value;

    if ($result) {
      $db->query("UPDATE Scripts_ran SET ran = '$value' WHERE name = '$name'");
    } else {
      $db->query("INSERT INTO Scripts_ran (name, ran) VALUES ('$name', $value)");
    }
  }
}