<?php

class Pubmed_parser {
  function __construct($config, $db) {
    require_once("libraries/run_check.php");
    
    $run_check = new Run_check();
    if ($run_check->should_run($config, $db, "pubmed")) {
      $this->parse($config, $db);

      $run_check->done_run($config, $db, "pubmed");
    }
  }

  function parse($config, $db) {
    require_once("libraries/parse_large_xml.php");

    $folder = $config["dir"] . $config["pubmed_dir"];

    if ($config["test"] === true) {
      $folder = $config["test_dir"] . $config["pubmed_dir"];
    }

    $xml_parser = new XML_parser();

    // Open the XML file
    if (($handle = @fopen($folder, "r")) === false) {
      echo "Error: XML file not found at: " . $folder;

      return;
    }

    // Get the nodestring incrementally from the xml file by defining a callback
    $articles = $xml_parser->nodeStringFromXMLFile($handle, "<PubmedArticle>", "</PubmedArticle>", $db, function($xml_parser, $db, $nodeText) {
      $simpleXML = simplexml_load_string($nodeText);

      // Title
      $title = "";

      $result = $simpleXML->xpath('//ArticleTitle');
      if (isset($result[0])) {
        $title = (string) $result[0];
      }
      
      // Abstract
      $abstract = "";

      $result = $simpleXML->xpath('//Abstract/AbstractText');
      
      if (isset($result[0])) {
        // Abstract contain HTML tags for some reason
        // Revert to plain XML string
        $xml = $result[0]->asXML();
        // Remove the tags
        $no_tags = trim(strip_tags($xml));
        
        // Remove any extra whitespace
        $abstract = preg_replace('/(\s)+/', ' ', $no_tags);
      }

      // Journal-Title
      $journal_title = "";

      $result = $simpleXML->xpath('//Journal/Title');
      if (isset($result[0])) {
        $journal_title = (string) $result[0];
      }

      // Journal-ISO
      $journal_iso = "";

      $result = $simpleXML->xpath('//Journal/ISOAbbreviation');
      if (isset($result[0])) {
        $journal_iso = (string) $result[0];  
      }      

      // ISSN
      $issn = "";

      $result = $simpleXML->xpath('//Journal/ISSN');
      if (isset($result[0])) {
        $issn = (string) $result[0];
      }

      // DOI
      $doi = "";

      $result = $simpleXML->xpath('//ArticleIdList/ArticleId[@IdType="doi"]');
      if (isset($result[0])) {
        $doi = (string) $result[0];

        $pattern = '/[0-9\.]+\/.*/';

        preg_match($pattern, $doi, $match);
        
        if (count($match) > 0) {
          $doi = $match[0];
        } else {
          $doi = "";
        }
      }

      // Publication date
      $day = "";

      $result = $simpleXML->xpath('//Journal/JournalIssue/PubDate/Day');
      if (isset($result[0])) {
        $day = (string) $result[0];
      }

      $month = "";

      $result = $simpleXML->xpath('//Journal/JournalIssue/PubDate/Month');
      if (isset($result[0])) {
        $month = (string) $result[0];
      }

      $year = "";

      $result = $simpleXML->xpath('//Journal/JournalIssue/PubDate/Year');
      if (isset($result[0])) {
        $year = (string) $result[0];
      }

      return array("title" => $title, "abstract" => $abstract, "doi" => $doi, "journal_title" => $journal_title, "journal_iso" => $journal_iso, "journal_issn" => $issn, "day" => $day, "month" => $month, "year" => $year);
    });

    fclose($handle);

    require_once("models/articles.php");
    
    $article_model = new Articles();
    
    foreach ($articles as $article) {
      $article_model->insert($db, $article, 'pubmed');
    }
  }
}