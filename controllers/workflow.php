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

class Workflow {
  function __construct() {
    require_once('config.php');
    require_once('database.php');

    $this->config = $config;

    $this->db = new Connector();

    $this->db->connect($this->config);
  }

  function run() {
    $this->init_base_articles();

    // Fix journals
    require_once('controllers/journal_delete.php');

    $journal_delete = new Journal_delete($this->db);

    // Remove excluded journals before search
    $journal_delete->remove_excluded_journals();
    // Fix the metadata of several journals before search
    $journal_delete->fix_journal_metadata();
    // Fix the assigned journals for some of the gathered articles
    $journal_delete->fix_journal_assignments();
    
    $this->remove_double_articles();

    // Search and fetch pubmed articles
    $this->run_pubmed_search();
    $this->run_pubmed_retrieve();

    // Fix the assigned journals for some of the gathered articles
    $journal_delete->fix_journal_assignments();

    // Remove any article that has an empty abstract or double entries
    // Only remove the robot gathered articles
    $this->remove_double_articles('robot');
  }

  function matcher() {
    require_once('controllers/matcher.php');

    $matcher = new Matcher($this->db);

    $matcher->random_match();
  }

  function init_base_articles() {
    // Read the initial articles from files
    require_once('controllers/init.php');

    $init = new Init($this->db);

    $init->run(true);
  }

  function remove_double_articles($type = false) {
    // Remove any article that has an empty abstract or double entries
    require_once('controllers/compare.php');

    $compare = new Compare($this->db);

    $compare->remove_empty_and_doubles($type);
  }

  function run_pubmed_search() {
    // Search PubMed
    require_once('pubmedAPI/search_ctrl.php');
    require_once('models/articles.php');

    $article_model = new Articles();

    $pubMedAPI = new PubMedAPI();
    $pubMedAPI->limit = 50;
    $pubMedAPI->show_urls = false;
    $pubMedAPI->display_mode = false;
    $pubMedAPI->return_mode = 'parsed';

    $retriever = new PubMedIDRetriever($db, $article_model, $pubMedAPI, $config['exclude_journal_array']);
    $retriever->run();
  }

  function run_pubmed_retrieve() {
    // Retrieve PubMed
    require_once('pubmedAPI/retrieve_ctrl.php');
    require_once('models/articles.php');

    $article_model = new Articles();

    $pubMedAPI = new PubMedAPI();
    $pubMedAPI->limit = 10000;
    $pubMedAPI->show_urls = false;
    $pubMedAPI->display_mode = false;
    $pubMedAPI->return_mode = 'parsed';

    $retriever = new PubMedArticleRetriever($db, $article_model, $pubMedAPI);
    $retriever->run();
  }
}