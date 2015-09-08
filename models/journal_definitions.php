<?php

class Journal_definitions {
  function find($db, $title, $issn = "", $iso = "") {
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

  function is_filled($db) {
    $result = $db->query("SELECT id FROM Journal_definitions");

    if ($result && $result->num_rows > 0) {
      return true;
    }

    return false;
  }

  function clear($db) {
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    $db->query("TRUNCATE TABLE Journal_definitions");
    $db->query("SET FOREIGN_KEY_CHECKS = 1");
  }
}

?>