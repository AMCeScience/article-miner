<?php

include('config.php');
include('models/articles.php');
require_once('database.php');

$db = new Connector();

$db->connect($config);

$article_model = new Articles();

$articles = $db->query("SELECT DISTINCT(journal) FROM Articles WHERE search_db = 'pubmed'");

foreach ($articles as $article) {
	$db->query("SELECT * FROM Articles WHERE search_db = 'robot' AND journal = ")
}