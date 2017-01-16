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

class Matcher {
  function __construct() {
    require_once('config.php');
    require_once('database.php');

    $this->config = $config;

    $this->db = new Connector();

    $this->db->connect($this->config);
  }

  function random_match() {
    require_once('models/articles.php');
    require_once('models/journals.php');

    $article_model = new Articles();
    $journal_model = new Journals();

    $journals = $journal_model->find_distinct($this->db, 'pubmed');

    while ($journal = $journals->fetch_array()) {
      $BD_articles = $db->query("SELECT count(*) AS count FROM Articles WHERE search_db = 'pubmed' AND journal = {$journal['journal']}");
      $BD_array = $BD_articles->fetch_array();
      $BD_count = (int) $BD_array['count'];

      $non_BD_articles = $this->create_array($db->query("SELECT id FROM Articles WHERE search_db = 'robot' AND journal = {$journal['journal']}"));
        
      if (count($non_BD_articles) === $BD_count) {
        continue;
      }
      
      if (count($non_BD_articles) < $BD_count) {
        echo 'Not enough entries for journal: ' . $journal['journal'] . ', selected ' . count($non_BD_articles) . ' articles<br/>';
          
        continue;
      }
      
      $non_BD_articles = array_map(function(&$item) {
        return $item['id'];
      }, $non_BD_articles);
      
      shuffle($non_BD_articles);
      
      $selected_articles = array_slice($non_BD_articles, 0, $BD_count * 2);
      
      $article_model->delete_inverse_ids($db, $selected_articles, $journal['journal'], 'robot');
    }
  }

  function create_array($mysql_obj) {
    $data_array = array();
    
    if ($mysql_obj === false) {
      return $data_array;
    }
    
    while ($data = $mysql_obj->fetch_array()) {
      $data_array[] = $data;
    }
    
    return $data_array;
  }
}