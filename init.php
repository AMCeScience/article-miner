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
    require_once("models/outcomes.php");
    require_once("models/alchemy_outcomes.php");
    
    $definitions_model = new Journal_definitions();
    $journal_model = new Journals();
    $article_model = new Articles();
    $outcomes_model = new Outcomes();
    $alchemy_outcomes_model = new AlchemyOutcomes();

    // Clear tables
    $definitions_model->clear($db);
    $journal_model->clear($db);
    $article_model->clear($db);
    $outcomes_model->clear($db);
    $alchemy_outcomes_model->clear($db);
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