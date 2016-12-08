<?php

header("Content-Type: text/html;charset=UTF-8");

include('config.php');
include('models/articles.php');
require_once('database.php');

$db = new Connector();

$db->connect($config);

$article_model = new Articles();

$journals = $db->query("SELECT DISTINCT(journal) FROM Articles WHERE search_db = 'pubmed'");

while ($journal = $journals->fetch_array()) {
    $BD_articles = $db->query("SELECT count(*) AS count FROM Articles WHERE search_db = 'pubmed' AND journal = {$journal['journal']}");
    $BD_array = $BD_articles->fetch_array();
    $BD_count = (int) $BD_array['count'];
	$non_BD_articles = create_array($db->query("SELECT id FROM Articles WHERE search_db = 'robot' AND journal = {$journal['journal']}"));
    
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