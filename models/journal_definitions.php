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
}

?>