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

class PubMedArticleRetriever {
  public $db;
  public $article_model;
  public $pubmed_api;
  public $excludes;

  function __construct($db, $article_model, $journal_model, $pubmed_api) {
    $this->db = $db;
    $this->article_model = $article_model;
    $this->journal_model = $journal_model;
    $this->pubmed_api = $pubmed_api;
  }

  function run() {
    $journals = $this->get_journals();
    
    foreach ($journals as $journal) {
      $pubmed_ids = $this->get_pubmed_ids($journal);
      $pubmed_articles = $this->get_pubmed_articles($pubmed_ids);
      
      if ($pubmed_articles === false) {
        echo $journal . ' <b>ARTICLES</b><br/>';

        continue;
      }


      foreach ($pubmed_articles as $article) {
        $article = array("title" => $article->title, "abstract" => $article->abstract, "doi" => $article->doi, "journal_title" => $article->title, "journal_iso" => $article->journalabbrev, "journal_issn" => $article->issn, "day" => '', "month" => $article->month, "year" => $article->year, "keywords" => '');

        $this->article_model->insert($this->db, $article, 'robot');
      }
    }
  }

  function get_pubmed_articles($pubmed_ids) {
    $results = $this->pubmed_api->fetch($pubmed_ids);
    
    if ($results->articles > 0) {
      return $results->articles;
    }

    return false;
  }

  function get_pubmed_ids($journal_id) {
    $articles = $this->article_model->get_pubmed_ids($this->db, $journal_id);

    if ($articles === false) {
      return false;
    }

    $article_id_array = array();

    while ($article = $articles->fetch_array()) {
      $article_id_array[] = $article['pubmed_id'];
    }

    return $article_id_array;
  }

  function get_journals() {
    $done = $this->journal_model->get_pubmed_fetched_journals($this->db);
    $all = $this->journal_model->get_pubmed_journals($this->db);

    $journal_array = array();

    while($journal = $all->fetch_array()) {
      if (!in_array($journal['journal_id'], $done)) {
        $journal_array[] = $journal['journal_id'];
      }
    }

    return $journal_array;
  }
}