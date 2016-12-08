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

header("Content-disposition: attachment; filename=articles.csv");
header("Content-Type: text/csv");

require_once("config.php");
require_once("database.php");

$db = new Connector();

$db->connect($config);

// Filter articles with an empty abstract
$where = ' WHERE abstract != ""';

//if (count($config['exclude_journal_issn']) > 0) {
//	$where .= ' AND j.issn NOT IN ("' . implode('", "', $config['exclude_journal_issn']) . '")';
//}
//
//if (count($config['exclude_journal_id']) > 0) {
//	$where .= ' AND j.id NOT IN ("' . implode('", "', $config['exclude_journal_id']) . '")';
//}

// Get articles from DB
$articles = $db->query("SELECT a.title, a.abstract, a.search_db
	FROM Articles AS a
	JOIN Journals AS j ON a.journal = j.id"
	. $where .
    "ORDER BY search_db"
);

$output = array();

while ($article = $articles->fetch_array()) {
	// Filter any non characters, retain a-z, - and spaces
	// Merge title and abstract into one CSV row
	$text = preg_replace("/[^a-zA-Z'\-\ ]/", "", $article["title"] . " " . $article["abstract"]);
	// Replace dashes within words with underscores
	$text = preg_replace("/(?<=\w)(-)(?=\w)/", "_", $text);
	// Replace double dashes with a space
	$text = preg_replace("/--/", " ", $text);
	// Remove single slashes that do not connect two words
	$text = preg_replace("/-/", "", $text);

	//$text = preg_replace("/(\.\ )/", " ", $article["title"] . " " . $article["abstract"]);
	//$text = preg_replace("/(\.$|,|;)/", "", $text);

	array_push($output, $text);
}

// Echo output as CSV
echo implode(",\n", $output) . "\n";