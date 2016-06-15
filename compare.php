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

$titles_and_dois = $article_model->get_titles_and_dois($db);

$titles_clean = array();
$dois_clean = array();

if ($titles_and_dois) {
  while ($row = $titles_and_dois->fetch_assoc()) {
    if (($row['title_count'] * 1) > 1) {
      $titles_clean[$row['id']] = $row['title_stripped'];
    }

    if (($row['doi_count'] * 1) > 1) {
      $dois_clean[$row['id']] = $row['doi'];
    }
  }
}

$unique_titles = array_unique($titles_clean);
$unique_dois = array_unique($dois_clean);

$ids = array();

foreach($unique_titles as $id => $title) {
  array_push($ids, $id);
}

foreach($unique_dois as $id => $doi) {
  array_push($ids, $id);
}

$ids = array_unique($ids);

if (count($ids) > 1) {
  echo "Removing " . count($ids) . " duplicates.<br/>";

  $article_model->delete($db, $ids);
}

echo "Time elapsed: " . (microtime(true) - $time_start) . "s <br/>";

echo "<a href='/index.php'>Back to index page</a>";