<?php

include('PubMedAPI.php');
include('../config.php');
include('../models/articles.php');
require_once('../database.php');

$db = new Connector();

$db->connect($config);

$article_model = new Articles();

$pubMedAPI = new PubMedAPI();
$pubMedAPI->limit = 50;
$pubMedAPI->show_urls = false;
$pubMedAPI->display_mode = false;
$pubMedAPI->return_mode = 'parsed';

// $retriever = new PubMedIDRetriever($db, $article_model, $pubMedAPI, $config['exclude_journal_array']);
$retriever = new PubMedArticleRetriever($db, $article_model, $pubMedAPI);
$retriever->run();

// $search_term = 'big data[TIAB]';
// $pubMedAPI = new PubMedAPI();
// $pubMedAPI->display_mode = false;
// // $pubMedAPI->data_type = 'json';
// $results = $pubMedAPI->search($search_term);

// foreach($results->articles as $r) {
// 	var_dump($r);
// 	exit;
//   echo $r->abstract . '<br/><br/>';
// }

class PubMedIDRetriever {
	public $db;
	public $article_model;
	public $pubmed_api;
	public $excludes;

	function __construct($db, $article_model, $pubmed_api, $excludes) {
		$this->db = $db;
		$this->article_model = $article_model;
		$this->pubmed_api = $pubmed_api;
		$this->excludes = $excludes;
	}

	function run() {
		$journals = $this->get_journals();
		$filtered = $this->filter_journals($journals);

		foreach ($filtered as $journal) {
			$query = $this->get_query($journal['issn'], $journal['title']);

			if ($query === false) {
				echo $journal['id'] . ' <b>ISSN/TITLE</b><br/>';
				
				continue;
			}

			$pubmed_ids = $this->execute_query($query);

			if ($pubmed_ids === false) {
				echo $journal['id'] . ' <b>IDS</b><br/>';
				
				continue;
			}

			$this->article_model->insert_pubmed_ids($this->db, $pubmed_ids, $journal['id']);
		}
	}

	function get_query($issn, $title) {
		$journal_id = $issn;

		if ($issn === "") {
			$journal_id = $title;
		}
		
		$range = $this->get_year_range($journal_id);

		if ($range === false) {
			return false;
		}

		list($lower_bound, $upper_bound) = $range;

		$query[] = "({$journal_id}[Journal]AND({$lower_bound}[PDAT]:{$upper_bound}[PDAT]))";

		return implode('OR', $query);
	}

	function get_year_range($journal_id) {
		$articles = $this->article_model->find_by_journal($this->db, $journal_id);

		if ($articles === false) {
			return false;
		}

		$article_years = array();

		while($article = $articles->fetch_array()) {
			if ($article['year'] != '') {
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
			return $results['ids'];
		}

		return false;
	}

	function get_journals() {
		include('../models/journals.php');

		$journal_model = new Journals();
		
		$results = $journal_model->get_relevant_journals($this->db);

		$journal_array = array();

		while($journal = $results->fetch_array()) {
			$journal_array[] = $journal;
		}

		return $journal_array;
	}

	function filter_journals($journals) {
		$excludes = $this->excludes;

		return array_filter($journals, function($journal) use ($excludes) {
			return !(in_array($journal['title'], $this->excludes) || in_array($journal['issn'], array_flip($this->excludes)));
		}, ARRAY_FILTER_USE_BOTH);
	}
}

class PubMedArticleRetriever {
	public $db;
	public $article_model;
	public $pubmed_api;
	public $excludes;

	function __construct($db, $article_model, $pubmed_api) {
		$this->db = $db;
		$this->article_model = $article_model;
		$this->pubmed_api = $pubmed_api;
	}

	function run() {
		$journals = $this->get_journals();

		foreach ($journals as $journal) {
			$pubmed_ids = $this->get_pubmed_ids($journal['journal_id']);
			$pubmed_articles = $this->get_pubmed_articles($pubmed_ids);

			if ($pubmed_articles === false) {
				echo $journal['id'] . ' <b>ARTICLES</b><br/>';

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
		include('../models/journals.php');

		$journal_model = new Journals();
		
		$results = $journal_model->get_pubmed_journals($this->db);

		$journal_array = array();

		while($journal = $results->fetch_array()) {
			$journal_array[] = $journal;
		}

		return $journal_array;
	}
}