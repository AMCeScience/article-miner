<?php

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