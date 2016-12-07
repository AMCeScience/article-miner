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

  function delete_by_article($db, $article_id) {
    $db->query("DELETE FROM Keywords_to_articles WHERE article IN ($article_id)");
  }
}