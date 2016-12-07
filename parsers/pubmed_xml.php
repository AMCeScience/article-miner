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

    $scanned_directory = array_diff(scandir($folder), array('..', '.'));

    if (!isset($scanned_directory)) {
      echo "Warning: no files found in: " . $folder . '<br/>';

      return;
    }

    $articles = array();

    foreach($scanned_directory as $file) {
      // Open the file
      if (($handle = @fopen($folder . "/" . $file, "r")) === false) {
        echo "Warning: file not found at: " . $folder . "/" . $file;

        return;
      }

      // Get the nodestring incrementally from the xml file by defining a callback
      $articles = $xml_parser->nodeStringFromXMLFile($handle, "<PubmedArticle>", "</PubmedArticle>", $db, function($xml_parser, $db, $nodeText) {
        $simpleXML = simplexml_load_string($nodeText, 'SimpleXMLElement', LIBXML_NOWARNING);

        if (!$simpleXML) {
          return;
        }

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

        $result = $simpleXML->xpath('//KeywordList/Keyword');

        $keywords = array();

        while(list( , $keyword) = each($result)) {
          array_push($keywords, (string) $keyword);
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
        
        if ($year === "") {
          $result = $simpleXML->xpath('//PubmedData/History/PubMedPubDate[@PubStatus="medline"]/Year');
          
          if (isset($result[0])) {
            $year = (string) $result[0];
          }
        }

        return array("title" => $title, "abstract" => $abstract, "doi" => $doi, "journal_title" => $journal_title, "journal_iso" => $journal_iso, "journal_issn" => $issn, "day" => $day, "month" => $month, "year" => $year, "keywords" => $keywords);
      });

      fclose($handle);
    }

    require_once("models/articles.php");
    
    $article_model = new Articles();
    
    foreach ($articles as $article) {
      $article_model->insert($db, $article, 'pubmed');
    }
  }
}