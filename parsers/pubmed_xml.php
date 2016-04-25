<?php

class Pubmed_parser {
  function run($config, $db) {
    require_once("models/articles.php");
    require_once("models/scriptchecker.php");

    $article_model = new Articles();
    $script_model = new ScriptChecker();

    if ($config["pubmed_list_reinit"] || !$article_model->is_filled($db) || !$script_model->has_ran($db, 'pubmed')) {
      echo "Reinitializing the pubmed list... ";
      $this->parse($config, $db);

      $script_model->set($db, 'pubmed', true);
      echo "done.<br/>";
    } else {
      echo "Skipping pubmed list initialisation.<br/>";
    }
  }

  function parse($config, $db) {
    require_once("parse_large_xml.php");

    $folder = $config["dir"] . $config["pubmed_dir"];

    if ($config["test"] === true) {
      $folder = $config["test_dir"] . $config["pubmed_dir"];
    }

    $xml_parser = new XML_parser();

    // Open the XML
    $handle = fopen($folder, "r");

    if (!$handle) {
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

?>