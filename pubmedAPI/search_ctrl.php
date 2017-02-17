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

class PubMedIDRetriever {
  public $db;
  public $article_model;
  public $pubmed_api;
  public $excludes;
  public $limit_modifier = 10;

  function __construct($db, $article_model, $journal_model, $pubmed_api, $excludes) {
    $this->db = $db;
    $this->article_model = $article_model;
    $this->journal_model = $journal_model;
    $this->pubmed_api = $pubmed_api;
    $this->excludes = $excludes;
  }

  function run() {
    $journals = $this->get_journals();
    $filtered = $this->filter_journals($journals);
    
    foreach ($filtered as $journal) {
      $query = $this->get_query($journal['id'], $journal['issn'], $journal['iso']);
      
      if ($query === false) {
        echo $journal['id'] . ' <b>ISSN/ISO</b><br/>';
        
        continue;
      }

      $limit = ($this->get_limit($journal['id']) * $this->limit_modifier);
      
      if ($limit === 0) {
        echo $journal['id'] . ' <b>LIMIT</b><br/>';

        continue;
      }

      $this->pubmed_api->limit = $limit;
      
      $results = $this->execute_query($query);
      
      if ($results === false) {
        echo $journal['id'] . ' <b>IDS</b><br/>';
        
        continue;
      }

      $pubmed_ids = $results['ids'];
      
      $match_on = $journal['issn'];

      if ($match_on == '') {
        $match_on = $journal['iso'];
      }

      if (strpos($results['query_interpretation'], $match_on) === false) {
        echo 'Journal does not match: ' . $results['query_interpretation'] . ' matching on: ' . $match_on . ' query: ' . $query . '<br/>';

        continue;
      }

      $this->article_model->insert_pubmed_ids($this->db, $pubmed_ids, $journal['id']);
    }
  }

  function get_query($journal_id, $issn, $iso) {
    $pubmed_journal_id = $issn;

    if ($issn === "") {
      $pubmed_journal_id = $iso;
    }

    if (strlen($pubmed_journal_id) < 1) {
      return false;
    }

    $range = $this->get_year_range($journal_id);

    if ($range === false) {
      return false;
    }

    list($lower_bound, $upper_bound) = $range;

    $query[] = "(\"{$pubmed_journal_id}\"[Journal]AND({$lower_bound}[PDAT]:{$upper_bound}[PDAT]))";

    return implode('OR', $query);
  }

  function get_limit($journal_id) {
    $articles = $this->article_model->find_by_journal($this->db, $journal_id);
    
    if ($articles === false) {
      return 0;
    }

    $count = 0;

    while ($article = $articles->fetch_array()) {
      $count++;
    }
    
    return $count;
  }

  function get_year_range($journal_id) {
    $articles = $this->article_model->find_by_journal($this->db, $journal_id);

    if ($articles === false) {
      return false;
    }

    $article_years = array();

    while($article = $articles->fetch_array()) {
      if ($article['year'] != '' && trim($article['year']) * 1 > 2010) {
        $article_years[] = $article['year'];
      }
    }

    $unique_years = array_unique($article_years);

    $lower_bound = min($unique_years);
    $upper_bound = max($unique_years);

    return array($lower_bound, $upper_bound);
  }

  function execute_query($query) {
    $results = $this->pubmed_api->search($query, 1, false);

    if ($results['total_results'] > 0) {
      return $results;
    }

    return false;
  }

  function get_journals() {
    $results = $this->journal_model->get_relevant_journals($this->db);

    $journal_array = array();

    while($journal = $results->fetch_array()) {
      $journal_array[] = $journal;
    }

    return $journal_array;
  }

  /**
  * Continue from previous search
  */
  function get_searched_journals() {
    $journals = $this->journal_model->get_pubmed_journals($this->db);

    $journals_array = array();

    if ($journals != false) {
      while ($data = $journals->fetch_array()) {
        $journals_array[] = $data['journal_id'];
      }
    }

    return $journals_array;
  }

  function filter_journals($journals) {
    $excludes = $this->excludes;

    $journals_done = $this->get_searched_journals();

    return array_filter($journals, function($journal) use ($excludes, $journals_done) {
      return !(in_array($journal['id'], $journals_done) || in_array($journal['title'], $this->excludes) || in_array($journal['issn'], array_flip($this->excludes)));
    }, ARRAY_FILTER_USE_BOTH);
  }
}