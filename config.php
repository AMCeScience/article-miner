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

// _run: should this parser be executed during a normal run
// _reinit: if the parser has run before, should the database be cleared and the parser run again
// _dir: location of the input files for the parser

$config["journal_list_run"] = true;
$config["journal_list_reinit"] = false;
$config["journal_list_dir"] = "files/journal_categories.csv";

$config["pubmed_central_run"] = true;
$config["pubmed_central_list_reinit"] = false;
$config["pubmed_central_dir"] = "pmc_result.xml";

$config["pubmed_run"] = true;
$config["pubmed_list_reinit"] = false;
$config["pubmed_dir"] = "pubmed_result.xml";

$config["ovid_run"] = true;
$config["ovid_list_reinit"] = false;
$config["ovid_dir"] = "ovid partials";

$config["wos_run"] = true;
$config["wos_list_reinit"] = false;
$config["wos_dir"] = "wos partials";

$config["scopus_run"] = true;
$config["scopus_list_reinit"] = false;
$config["scopus_dir"] = "scopus partials";

// File dir, appended to every _dir listed above
$config["dir"] = "files/full results/";
// File dir for test mode, appended to every _dir listed above
$config["test_dir"] = "files/test folder/";

// Set test mode
$config["test"] = true;

$config["database"]["ip"] = "localhost";
$config["database"]["username"] = "root";
$config["database"]["password"] = "root";
$config["database"]["schema"] = "miner_alchemy";

if (isset($_GET) && isset($_GET["reinit"]) && $_GET["reinit"] == "true") {
  $config["journal_list_reinit"] = true;
  $config["pubmed_central_list_reinit"] = true;
  $config["ovid_list_reinit"] = true;
  $config["pubmed_list_reinit"] = true;
  $config["wos_list_reinit"] = true;
  $config["scopus_list_reinit"] = true;
}

// Alchemy config
$config["alchemy_key_dir"] = "/Users/Allard/workspace/miner/alchemyAPI/";
$config["alchemy_reinit"] = false;
$config["alchemy_spending_transactions_per_run"] = 36;
$config["alchemy_safety_catch"] = 10;
$config["alchemy_collect"] = array("taxonomy", "keywords", "entities", "concepts");