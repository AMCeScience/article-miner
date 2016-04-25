<?php

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
require_once("parsers/parse_journal_list.php");

$journal_list_parser = new Journal_list();
$journal_list_parser->run($config, $db);

// Parsing pubmed central XML
require_once("parsers/pubmed_central_xml.php");

$pubmed_central_parser = new Pubmed_central_parser();
$pubmed_central_parser->run($config, $db);

// Parsing Ovid XML
require_once("parsers/ovid_xml.php");

$ovid_parser = new Ovid_parser();
$ovid_parser->run($config, $db);

// Parsing Pubmed XML
require_once("parsers/pubmed_xml.php");

$pubmed_parser = new Pubmed_parser();
$pubmed_parser->run($config, $db);

// // Parsing WoS tab
// require_once("parsers/webofscience_tab.php");

// $wos_parser = new WoS_parser();
// $wos_parser->run($config, $db);

// // Parsing Scopus CSV
// require_once("parsers/scopus_csv.php");

// $scopus_parser = new Scopus_parser();
// $scopus_parser->run($config, $db);

echo "Time elapsed: " . (microtime(true) - $time_start) . "s";

?>