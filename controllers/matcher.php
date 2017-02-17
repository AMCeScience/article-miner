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
  function __construct($db) {
    include('config.php');

    $this->config = $config;

    $this->db = $db;

    if ($db === null) {
      require_once('database.php');

      $this->db = new Connector();

      $this->db->connect($this->config);
    }
  }

  function random_match() {
    require_once('models/articles.php');
    require_once('models/journals.php');

    $article_model = new Articles();
    $journal_model = new Journals();

    $article_model->reset_batch($this->db);

    $cur_batch = 1;

    $journals = $journal_model->find_distinct($this->db, 'pubmed');

    $journal_array = $this->create_array($journals);

    $BD_journal_array = array();

    foreach ($journal_array as $journal) {
      $BD_articles = $this->db->query("SELECT count(*) AS count FROM Articles WHERE search_db = 'pubmed' AND journal = {$journal['journal']}");
      $BD_array = $BD_articles->fetch_array();

      $BD_journal_array[$journal['journal']] = (int) $BD_array['count'];
    }

    while ($cur_batch <= $this->config['folds']) {
      foreach ($BD_journal_array as $journal => $BD_count) {
        $NBD_articles = $this->create_array($this->db->query("SELECT id FROM Articles WHERE search_db = 'robot' AND journal = {$journal} AND batch = 0"));
        $NBD_count = count($NBD_articles);

        // Not enough non big data articles, skip these on first batch
        if ($cur_batch === 1 && (($BD_count * $this->config['folds'] * $this->config['fold_multiplication'] > $NBD_count) || $NBD_count == 0)) {
          // Remove from array
          unset($BD_journal_array[$journal]);

          continue;
        }

        $NBD_articles = array_map(function(&$item) {
          return $item['id'];
        }, $NBD_articles);
        
        shuffle($NBD_articles);
        
        $selected_articles = array_slice($NBD_articles, 0, $BD_count * $this->config['fold_multiplication']);

        $article_model->set_batch($this->db, $cur_batch, $selected_articles);
        $article_model->set_batch_by_journal($this->db, 99, $journal, 'pubmed');
      }

      $cur_batch++;
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