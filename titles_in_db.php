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

$time_start = microtime(true);

require_once("config.php");
require_once("database.php");

$db = new Connector();

$db->connect($config);

require_once("models/articles.php");

$article_model = new Articles();

$contents = file('files/titles.txt');

foreach($contents as $line) {
  $found_articles = $article_model->find_by_stripped_title($db, $line);

  if (!$found_articles) {
    echo $line . '<br/>';

    continue;
  }

  $count = 0;

  while ($row = $found_articles->fetch_assoc()) {
    $count++;
  }

  echo $count . '<br/>';
}