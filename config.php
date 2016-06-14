<?php

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

$config["dir"] = "files/full results/";
$config["test_dir"] = "files/test folder/";

$config["test"] = true;

$config["database"]["ip"] = "localhost";
$config["database"]["username"] = "root";
$config["database"]["password"] = "root";
$config["database"]["schema"] = "miner";

if (isset($_GET) && isset($_GET["reinit"]) && $_GET["reinit"] == "true") {
  $config["journal_list_reinit"] = true;
  $config["pubmed_central_list_reinit"] = true;
  $config["ovid_list_reinit"] = true;
  $config["pubmed_list_reinit"] = true;
  $config["wos_list_reinit"] = true;
  $config["scopus_list_reinit"] = true;
}