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

error_reporting(E_ALL);

$time_start = microtime(true);

require_once("config.php");
require_once("database.php");

$db = new Connector();

$db->connect($config);

if (isset($_GET) && isset($_GET["reinit"]) && $_GET["reinit"] == "true") {
  // Reset database
  require_once("models/journal_definitions.php");
  require_once("models/journals.php");
  require_once("models/articles.php");
  
  $definitions_model = new Journal_definitions();
  $journal_model = new Journals();
  $article_model = new Articles();

  // Clear tables
  $definitions_model->clear($db);
  $journal_model->clear($db);
  $article_model->clear($db);
}

// Parsing the journal list csv file
if ($config["journal_list_run"] === true) {
  require_once("parsers/parse_journal_list.php");

  new Journal_list($config, $db);
}

// Parsing pubmed central XML
if ($config["pubmed_central_run"] === true) {
  require_once("parsers/pubmed_central_xml.php");

  new Pubmed_central_parser($config, $db);
}

// Parsing Ovid XML
if ($config["ovid_run"] === true) {
  require_once("parsers/ovid_xml.php");

  new Ovid_parser($config, $db);
}

// Parsing Pubmed XML
if ($config["pubmed_run"] === true) {
  require_once("parsers/pubmed_xml.php");

  new Pubmed_parser($config, $db);
}

// Parsing Web of Science/Web of Knowledge tab delimited file
if ($config["wos_run"] === true) {
  require_once("parsers/webofscience_tab.php");

  new WoS_parser($config, $db);
}

// Parsing Scopus CSV
if ($config["scopus_run"] === true) {
  require_once("parsers/scopus_csv.php");

  new Scopus_parser($config, $db);
}

echo "Time elapsed: " . (microtime(true) - $time_start) . "s <br/>";

echo "<a href='/index.php'>Back to index page</a>";