<?php

// Article Miner, a document parser for Pubmed, Pubmed Central, Ovid, Scopus, and Web of Science
// Copyright (C) 2016 Allard van Altena

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.

// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

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
    
    if (!isset($scanned_directory)) {
      echo "Warning: no files found in: " . $folder . '<br/>';

      return;
    }

    require_once("models/articles.php");
    
    $article_model = new Articles();

    foreach($scanned_directory as $file) {
      // Open the XML
      if (($handle = @fopen($folder . "/" . $file, "r")) === false) {
        echo "Warning: XML file not found at: " . $folder . "/" . $file;

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
          foreach ($result as $item) {
            // Abstract contain HTML tags for some reason
            // Revert to plain XML string
            $xml = $item->asXML();
            // Remove the tags
            $no_tags = trim(strip_tags($xml));
            
            // Remove any extra whitespace
            $abstract .= ' ' . preg_replace('/(\s)+/', ' ', $no_tags);
          }
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