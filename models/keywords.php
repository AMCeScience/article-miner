<?php

class Keywords {
  function find($db, $keyword) {
    $keyword_fixed = addslashes($keyword);

    $sql = "SELECT * FROM Keywords WHERE keyword LIKE '%$keyword_fixed%'";

    $result = $db->query($sql);

    if ($result) {
      return $result->fetch_array();
    } 

    return false;
  }

  function insert($db, $keyword, $article_id) {
    $existing_keyword = $this->find($db, $keyword);

    if ($existing_keyword) {
      return $existing_keyword["id"];
    }

    $keyword_fixed = addslashes($keyword);

    $sql = "INSERT INTO Keywords (keyword) VALUES ('$keyword_fixed')";
    
    if ($result = $db->query($sql)) {
      $keyword_id = $db->connection->insert_id;

      $db->query("INSERT INTO Keywords_to_articles (article, keyword) VALUES ('$article_id', '$keyword_id')");

      return $keyword_id;
    }

    return false;
  }
}

?>