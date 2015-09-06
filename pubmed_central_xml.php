<?php

class Pubmed_central_parser {
  function run($config, $db) {
    if ($config["pubmed_central_list_init"]) {
      echo "Reinitializing the pubmed central list... ";
      $this->parse($config, $db);
      echo "done.<br/>";
    } else {
      echo "Skipping pubmed central list initialisation.<br/>";
    }
  }

  function parse($config, $db) {
    require_once("parse_large_xml.php");

    $xml_parser = new XML_parser();

    // Open the XML
    $handle = fopen($config["pubmed_central_dir"], "r");

    // Get the nodestring incrementally from the xml file by defining a callback
    $articles = $xml_parser->nodeStringFromXMLFile($handle, "<article", "</article>", $db, function($xml_parser, $db, $nodeText){
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
        $abstract = (string) $result[0];
      }

      // Keywords
      $keywords = array();

      $result = $simpleXML->xpath('//kwd-group/kwd');
      while(list( , $keyword) = each($result)) {
        array_push($keywords, $keyword);
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

      return array("title" => $title, "abstract" => $abstract, "keywords" => $keywords, "journal_title" => $journal_title, "journal_iso" => $journal_iso, "issn" => $issn);
    });

    fclose($handle);

    require_once("journals.php");
    
    $journal_model = new Journals();
    
    foreach ($articles as $article) {
      
    }
  }
}

?>