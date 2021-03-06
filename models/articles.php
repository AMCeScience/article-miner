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

  // Get titles (stripped of all characters other than a-z) and DOIs of all articles in DB
  // Also, count available (i.e. non-empty) titles and DOIs
  function get_titles_and_dois($db) {
    $result = $db->query("SELECT id, title_stripped, doi, (
          SELECT count(inner_article.id) FROM Articles AS inner_article 
          WHERE inner_article.doi = outer_article.doi AND inner_article.doi != '' 
          GROUP BY inner_article.doi
        ) as doi_count, (
          (SELECT count(inner_article.id) FROM Articles AS inner_article 
          WHERE inner_article.title_stripped = outer_article.title_stripped AND inner_article.title_stripped != '' 
          GROUP BY inner_article.title_stripped)
        ) as title_count
      FROM Articles AS outer_article
        WHERE (
          SELECT count(inner_article.id) FROM Articles AS inner_article 
          WHERE inner_article.title_stripped = outer_article.title_stripped AND inner_article.title_stripped != '' 
          GROUP BY inner_article.title_stripped
        ) > 1 OR (
          SELECT count(inner_article.id) FROM Articles AS inner_article 
          WHERE inner_article.doi = outer_article.doi AND inner_article.doi != '' 
          GROUP BY inner_article.doi
        ) > 1
    ");

    return $result;
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

      return $article_id;
    }

    return false;
  }

  function delete_by_db($db, $db_name) {
    $db->query("DELETE FROM Articles WHERE search_db = '$db_name'");
  }

  // Delete (an) article(s)
  // Takes either a single id or array of ids as input
  function delete($db, $ids) {
    $ids = implode(",", $ids);

    $db->query("DELETE FROM Articles WHERE id IN ($ids)");
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