<?php

class Ovid_parser {
  function run($config, $db) {
    require_once("models/articles.php");
    require_once("models/scriptchecker.php");

    $article_model = new Articles();
    $script_model = new ScriptChecker();

    if ($config["ovid_list_reinit"] || !$article_model->is_filled($db) || !$script_model->has_ran($db, 'ovid')) {
      echo "Reinitializing the ovid list... ";
      $this->parse($config, $db);

      $script_model->set($db, 'ovid', true);
      echo "done.<br/>";
    } else {
      echo "Skipping ovid central list initialisation.<br/>";
    }
  }

  function parse($config, $db) {
    $folder = $config["dir"] . $config["ovid_dir"];

    if ($config["test"] === true) {
      $folder = $config["test_dir"] . $config["ovid_dir"];
    }

    require_once("parse_large_xml.php");

    $xml_parser = new XML_parser();

    $scanned_directory = array_diff(scandir($folder), array('..', '.'));

    require_once("models/articles.php");
    
    $article_model = new Articles();

    foreach($scanned_directory as $file) {
      // Open the XML
      $handle = fopen($folder . "/" . $file, "r");

      if (!$handle) {
        echo "Error: XML file not found at: " . $folder;

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

?>