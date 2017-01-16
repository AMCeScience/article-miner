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

class Articles {
  // Count all articles
  function count($db) {
    $result = $db->query("SELECT count(id) AS count FROM Articles");

    if ($result) {
      return $result->fetch_array()['count'];
    }

    return 0;
  }

  // Count all articles with non-empty abstract field
  function count_not_empty($db) {
    $result = $db->query("SELECT count(id) AS count FROM Articles WHERE abstract != ''");

    if ($result) {
      return $result->fetch_array()['count'];
    }

    return 0;
  }

  function count_by_journal($db, $search_db) {
    $result = $db->query("SELECT journal, count(id) AS count
      FROM articles
      WHERE search_db = '{$search_db}'
      GROUP BY journal
      ORDER BY count DESC");

    if ($result) {
      $return_array = array();

      while ($journal = $result->fetch_array()) {
        $return_array[$journal['journal']] = $journal['count'];
      }

      return $return_array;
    }

    return false;
  }

  function find_by_journal($db, $journal_id) {
    $result = $db->query("SELECT * 
      FROM Articles AS a 
      JOIN Journals AS j ON a.journal = j.id
      WHERE j.id = '{$journal_id}'
    ");

    if ($result) {
      return $result;
    }

    return false;
  }

  // Get an article by its id
  function find_by_id($db, $id) {
    $result = $db->query("SELECT * FROM Articles WHERE id = $id");

    if ($result) {
      return $result->fetch_array();
    } 

    return false;
  }

  function find_by_stripped_title($db, $title_stripped) {
    $title_trimmed = trim($title_stripped);

    $result = $db->query("SELECT * FROM Articles WHERE title_stripped LIKE '%{$title_trimmed}%' AND abstract != ''");

    if ($result) {
      return $result;
    }

    return false;
  }

  function delete_empty_abstracts($db) {
    $sql = "DELETE FROM articles WHERE abstract = ''";

    $db->query($sql);

    return $db->connection->affected_rows;
  }

  function delete_double_title($db, $search_db = false) {
    $clause = "";

    if ($search_db !== false) {
      $clause = " AND search_db = '{$search_db}'";
    }

    $sql = "DELETE FROM articles
      WHERE id IN (
        SELECT id FROM (SELECT id
        FROM Articles AS outer_article
        WHERE title != ''{$clause}
        GROUP BY title
        HAVING count(*) > 1
        ORDER BY id) AS x
      )";

      $db->query($sql);

      return $db->connection->affected_rows;
  }

  function delete_double_doi($db, $search_db = false) {
    $clause = "";

    if ($search_db !== false) {
      $clause = " AND search_db = '{$search_db}'";
    }

    $sql = "DELETE FROM articles
      WHERE id IN (
        SELECT id FROM (SELECT id
        FROM Articles AS outer_article
        WHERE doi != ''{$clause}
        GROUP BY doi
        HAVING count(*) > 1
        ORDER BY id) AS x
      )";

      $db->query($sql);

      return $db->connection->affected_rows;
  }

  // Insert an articles
  // Takes an article as input as formatted by the parsers
  // Also inserts the journal related to the article
  function insert($db, $article, $database) {
    require_once("journals.php");

    $journal_model = new Journals();

    // Insert journal, if the journal already exists the existing journal_id is returned
    $journal_id = $journal_model->insert($db, $article["journal_title"], $article["journal_iso"], $article["journal_issn"]);

    // Insert article
    // Strip title of any characters other than a-z, used to compare articles to each other
    $title_stripped = preg_replace("/[^a-z]/i", "", strtolower($article["title"]));
    $title_fixed = addslashes($article["title"]);
    $abstract_fixed = addslashes($article["abstract"]);

    $sql = "INSERT INTO Articles (title, title_stripped, abstract, journal, day, month, year, doi, search_db) 
      VALUES ('$title_fixed', '$title_stripped', '$abstract_fixed', '$journal_id', '{$article['day']}', 
        '{$article['month']}', '{$article['year']}', '{$article['doi']}', '$database')";
    
    if ($result = $db->query($sql)) {
      $article_id = $db->connection->insert_id;

      // require_once("keywords.php");

      // $keyword_model = new Keywords();

      // // Insert keywords
      // foreach ($article["keywords"] as $keyword) {
      //   $keyword_model->insert($db, $keyword, $article_id);
      // }

      return $article_id;
    }

    return false;
  }

  function pubmed_id_exists($db, $pubmed_id) {
    $sql = "SELECT COUNT(*) AS count FROM Pubmed_articles WHERE pubmed_id = {$pubmed_id}";

    if ($result = $db->query($sql)) {
      $data_array = $result->fetch_array();

      return $data_array['count'] > 0;
    }

    return false;
  }

  function insert_pubmed_ids($db, $pubmed_ids, $journal_id) {
    if (!is_array($pubmed_ids)) {
      $pubmed_ids = array($pubmed_ids);
    }

    $pubmed_ids = array_unique($pubmed_ids);

    foreach($pubmed_ids as $key => $pubmed_id) {
      if ($this->pubmed_id_exists($db, $pubmed_id)) {
        unset($pubmed_ids[$key]);
      }
    }
    
    $query = array();

    foreach ($pubmed_ids as $pubmed_id) {
      $query[] = "'" . $pubmed_id . "','" . $journal_id . "'";
    }

    if (count($pubmed_ids) > 0) {
      $db->query("INSERT INTO Pubmed_articles (pubmed_id, journal_id) VALUES (" . implode("),(", $query) . ")");
    }
  }

  function get_pubmed_ids($db, $journal_id) {
    $sql = "SELECT * FROM Pubmed_articles WHERE journal_id = {$journal_id}";

    if ($result = $db->query($sql)) {
      return $result;
    }

    return false;
  }

  function delete_by_db($db, $db_name) {
    $db->query("DELETE FROM Articles WHERE search_db = '$db_name'");

    return $db->connection->affected_rows;
  }

  function delete_inverse_ids($db, $ids, $journal_id, $search_db) {
    $ids = implode(",", $ids);
    
    $db->query("DELETE FROM Articles WHERE journal = {$journal_id} AND id NOT IN ({$ids}) AND search_db = '{$search_db}'");

    return $db->connection->affected_rows;
  }
  
  // Delete (an) article(s)
  // Takes either a single id or array of ids as input
  function delete($db, $ids) {
    require_once("keywords.php");

    $keyword_model = new Keywords();

    foreach($ids as $article_id) {
      $keyword_model->delete_by_article($db, $article_id);
    }

    $ids = implode(",", $ids);

    $db->query("DELETE FROM Articles WHERE id IN ($ids)");

    return $db->connection->affected_rows;
  }

  function delete_by_journal($db, $journal_id) {
    $db->query("DELETE FROM Articles WHERE journal = {$journal_id}");

    return $db->connection->affected_rows;
  }

  // Checks whether there are any rows in the articles table
  function is_filled($db) {
    $result = $db->query("SELECT id FROM Articles");

    if ($result && $result->num_rows > 0) {
      return true;
    }

    return false;
  }

  // Truncate the article table
  function clear($db) {
    // Clear table
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    $db->query("TRUNCATE TABLE Articles");
    $db->query("SET FOREIGN_KEY_CHECKS = 1");
  }
}