<?php

class Pubmed_central_parser {
  function run($config, $db) {
    require_once("models/articles.php");
    require_once("models/scriptchecker.php");

    $article_model = new Articles();
    $script_model = new ScriptChecker();

    if ($config["pubmed_central_list_reinit"] || !$article_model->is_filled($db) || !$script_model->has_ran($db, 'pubmed_central')) {
      echo "Reinitializing the pubmed central list... ";
      $this->parse($config, $db);

      $script_model->set($db, 'pubmed_central', true);
      echo "done.<br/>";
    } else {
      echo "Skipping pubmed central list initialisation.<br/>";
    }
  }

  function parse($config, $db) {
    require_once("parse_large_xml.php");

    $folder = $config["dir"] . $config["pubmed_central_dir"];

    if ($config["test"] === true) {
      $folder = $config["test_dir"] . $config["pubmed_central_dir"];
    }

    $xml_parser = new XML_parser();

    // Open the XML
    $handle = fopen($folder, "r");

    if (!$handle) {
      echo "Error: XML file not found at: " . $folder;

      return;
    }

    // Get the nodestring incrementally from the xml file by defining a callback
    $articles = $xml_parser->nodeStringFromXMLFile($handle, "<article", "</article>", $db, function($xml_parser, $db, $nodeText) {
      $simpleXML = simplexml_load_string($nodeText);

      // Title
      $title = "";

      $result = $simpleXML->xpath('//title-group/article-title');
      if (isset($result[0])) {
        $title = (string) $result[0];
      }
      
      // Abstract
      $abstract = "";

      $result = $simpleXML->xpath('//abstract');
      
      if (isset($result[0])) {
        // Abstract contain HTML tags for some reason
        // Revert to plain XML string
        $xml = $result[0]->asXML();
        // Remove the tags
        $no_tags = trim(strip_tags($xml));
        
        // Remove any extra whitespace
        $abstract = preg_replace('/(\s)+/', ' ', $no_tags);
      }

      // Keywords
      $keywords = array();

      $result = $simpleXML->xpath('//kwd-group/kwd');
      while(list( , $keyword) = each($result)) {
        array_push($keywords, (string) $keyword);
      }

      // Journal-Title
      $journal_title = "";

      $result = $simpleXML->xpath('//journal-title');
      if (isset($result[0])) {
        $journal_title = (string) $result[0];
      }

      // Journal-ISO
      $journal_iso = "";

      $result = $simpleXML->xpath('//journal-meta/journal-id[@journal-id-type="iso-abbrev"]');
      if (isset($result[0])) {
        $journal_iso = (string) $result[0];  
      }      

      // ISSN
      $issn = "";

      $result = $simpleXML->xpath('//issn[@pub-type="ppub"]');
      if (isset($result[0])) {
        $issn = (string) $result[0];
      }

      $doi = "";

      $result = $simpleXML->xpath('//article-meta/article-id[@pub-id-type="doi"]');
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

      $result = $simpleXML->xpath('//pub-date/day');
      if (isset($result[0])) {
        $day = (string) $result[0];
      }

      $month = "";

      $result = $simpleXML->xpath('//pub-date/month');
      if (isset($result[0])) {
        $month = (string) $result[0];
      }

      $year = "";

      $result = $simpleXML->xpath('//pub-date/year');
      if (isset($result[0])) {
        $year = (string) $result[0];
      }

      return array("title" => $title, "abstract" => $abstract, "doi" => $doi, "journal_title" => $journal_title, "journal_iso" => $journal_iso, "journal_issn" => $issn, "day" => $day, "month" => $month, "year" => $year);
    });

    fclose($handle);

    require_once("models/articles.php");
    
    $article_model = new Articles();
    
    foreach ($articles as $article) {
      $article_model->insert($db, $article, 'pmc');
    }
  }
}

?>