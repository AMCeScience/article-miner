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

class Journal_delete {
  function __construct($db = null) {
    include('config.php');

    $this->config = $config;

    $this->db = $db;

    if ($db === null) {
      require_once('database.php');

      $this->db = new Connector();

      $this->db->connect($this->config);
    }
  }

  function remove_excluded_journals() {
    require_once('models/journals.php');
    require_once('models/articles.php');

    $journal_model = new Journals();
    $article_model = new Articles();

    $journal_ids = $journal_model->find_excluded_ids($this->db, $this->config['exclude_journal_array']);

    $articles_removed = 0;
    $journals_removed = 0;

    foreach ($journal_ids as $journal_id) {
      $articles_removed += $article_model->delete_by_journal($this->db, $journal_id);
      $journals_removed += $journal_model->delete($this->db, $journal_id);
    }

    return [$articles_removed, $journals_removed];
  }

  function fix_journal_metadata() {
    foreach ($this->config['fix_journal_metadata'] as $item) {
      $from = $item['from'];
      $to = $item['to'];

      $this->db->query("UPDATE Journals SET {$from[0]} = '{$from[1]}' WHERE {$to[0]} = '{$to[1]}'");
    }
  }

  function fix_journal_assignments() {
    $this->db->query("SET FOREIGN_KEY_CHECKS = 0");

    // Fixing journals that got duplicated
    foreach ($this->config['fix_duplicated_journals'] as $from => $to) {
      $this->db->query("UPDATE articles SET journal = {$from} WHERE journal = {$to}");
    }

    // Rename search_db from pubmed_central to pubmed
    $this->db->query("UPDATE articles SET search_db = 'pubmed' WHERE search_db = 'pubmed_central';");
    // Remove robot articles of which the journal does not map to any known journals
    $this->db->query("DELETE FROM Articles
          WHERE id IN (
            SELECT id FROM (
              SELECT a.id
              FROM articles AS a
              LEFT JOIN articles AS b ON a.journal = b.journal AND b.search_db = 'pubmed'
              WHERE a.search_db = 'robot' AND b.journal IS NULL
            ) AS x
          );");

    // Remove any journals that have no articles associated with them
    $this->db->query("
      DELETE FROM Journals
      WHERE id IN (
        SELECT * FROM (
        SELECT j1.id FROM Journals AS j1
        WHERE j1.id NOT IN (
          SELECT DISTINCT(journal) FROM Articles
        )) x
      )"
    );
    
    $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
  }
}