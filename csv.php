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

// Get articles from DB
$articles = $db->query("SELECT * FROM Articles");

$output = array();

while ($article = $articles->fetch_array()) {
  // Filter articles with an empty abstract
  if ($article["abstract"] != "") {
    // Filter any non characters, retain a-z, - and spaces
    // Merge title and abstract into one CSV row
    $text = preg_replace("/[^a-zA-Z'\-\ ]/", "", $article["title"] . " " . $article["abstract"]);

    array_push($output, $text);
  }
}

// Echo output as CSV
echo implode(",\n", $output) . "\n";