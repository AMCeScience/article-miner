<?php

$time_start = microtime(true);

require_once("config.php");
require_once("database.php");

$db = new Connector();

$db->connect($config);

// Parsing the journal list csv file
require_once("parsers/parse_journal_list.php");

$journal_list_parser = new Journal_list();
$journal_list_parser->run($config, $db);

// Parsing pubmed central XML
require_once("parsers/pubmed_central_xml.php");

$pubmed_central_parser = new Pubmed_central_parser();
$pubmed_central_parser->run($config, $db);

echo "Time elapsed: " . (microtime(true) - $time_start) . "s";

?>