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
  // require_once("models/alchemy_outcomes.php");
  
  $definitions_model = new Journal_definitions();
  $journal_model = new Journals();
  $article_model = new Articles();
  // $alchemy_outcome_model = new AlchemyOutcomes();

  // Clear tables
  $definitions_model->clear($db);
  $journal_model->clear($db);
  $article_model->clear($db);
  // $alchemy_outcome_model->clear($db);
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

echo "Time elapsed: " . (microtime(true) - $time_start) . "s";

?>