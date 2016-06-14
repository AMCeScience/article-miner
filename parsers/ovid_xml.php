<?php

class Ovid_parser {
  function __construct($config, $db) {
    require_once("libraries/run_check.php");
    
    $run_check = new Run_check();
    if ($run_check->should_run($config, $db, "ovid")) {
      $this->parse($config, $db);

      $run_check->done_run($config, $db, "ovid");
    }
  }

  function parse($config, $db) {
    $folder = $config["dir"] . $config["ovid_dir"];

    if ($config["test"] === true) {
      $folder = $config["test_dir"] . $config["ovid_dir"];
    }

    require_once("libraries/parse_large_xml.php");

    $xml_parser = new XML_parser();

    $scanned_directory = array_diff(scandir($folder), array('..', '.'));

    require_once("models/articles.php");
    
    $article_model = new Articles();

    foreach($scanned_directory as $file) {
      // Open the XML
      if (($handle = @fopen($folder . "/" . $file, "r")) === false) {
        echo "Error: XML file not found at: " . $folder . "/" . $file;

        return;
      }

      // Get the nodestring incrementally from the xml file by defining a callback
      $articles = $xml_parser->nodeStringFromXMLFile($handle, "<record ", "</record>", $db, function($xml_parser, $db, $nodeText) {
        $simpleXML = simplexml_load_string($nodeText);
        
        if (!$simpleXML) {
          echo $nodeText;
        }

        // Title
        $title = "";

        $result = $simpleXML->xpath('//F[@L="Title"]/D');
        
        if (isset($result[0])) {
          $title = (string) $result[0];
        }

        // Abstract
        $abstract = "";

        $result = $simpleXML->xpath('//F[@L="Abstract"]/D');
        
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

        $result = $simpleXML->xpath('//F[@L="Source"]/D');
        if (isset($result[0])) {
          $journal_title = substr($result[0]->asXML(), 12, strpos($result[0]->asXML(), '<T>') - 12);
        }

        // Journal-ISO
        $journal_iso = "";

        // $result = $simpleXML->xpath('//journal-meta/journal-id[@journal-id-type="iso-abbrev"]');
        // if (isset($result[0])) {
        //   $journal_iso = (string) $result[0];  
        // }      

        // ISSN
        $issn = "";

        $result = $simpleXML->xpath('//F[@L="ISSN"]/D');
        if (isset($result[0])) {
          $issn = (string) $result[0];
        }

        // DOI
        $doi = "";

        $result = $simpleXML->xpath('//F[@L="Digital Object Identifier"]/D');
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
        $month = "";
        $year = "";

        $result = $simpleXML->xpath('//F[@L="Year of Publication"]/D');
        if (isset($result[0])) {
          $year = (string) $result[0];
        } else {
          $result = $simpleXML->xpath('//F[@L="Date of Publication"]/D');
          if (isset($result[0])) {
            $year = (string) $result[0];
            $year = preg_replace('/[^\d]/', '', $year);
            $year = substr($year, -4);
          }
        }

        return array("title" => $title, "abstract" => $abstract, "doi" => $doi, "journal_title" => $journal_title, "journal_iso" => $journal_iso, "journal_issn" => $issn, "day" => $day, "month" => $month, "year" => $year);
      });

      fclose($handle);

      foreach ($articles as $article) {
        $article_model->insert($db, $article, 'ovid');
      }
    }
  }
}