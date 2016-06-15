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

class Scopus_parser {
  function __construct($config, $db) {
    require_once("libraries/run_check.php");
    
    $run_check = new Run_check();
    if ($run_check->should_run($config, $db, "scopus")) {
      $this->parse($config, $db);

      $run_check->done_run($config, $db, "scopus");
    }
  }

  function parse($config, $db) {
    $folder = $config["dir"] . $config["scopus_dir"];

    if ($config["test"] === true) {
      $folder = $config["test_dir"] . $config["scopus_dir"];
    }

    // Hard set PHP config, used for CSV file line endings
    ini_set("auto_detect_line_endings", true);

    $scanned_directory = array_diff(scandir($folder), array('..', '.'));

    $articles = array();

    foreach($scanned_directory as $file) {
      // Open the file
      if (($handle = @fopen($folder . "/" . $file, "r")) === false) {
        echo "Error: file not found at: " . $folder . "/" . $file;

        return;
      }
      
      while (($line = fgets($handle))) {
        // Split csv
        $line = str_getcsv($line);

        // Check if this is the first line
        if ($line[1] === 'Title') {
          continue;
        }
        
        // Title
        $title = $line[1];

        // Abstract
        $abstract = $line[15];

        // Journal-Title
        $journal_title = $line[3];

        // Journal-ISO
        $journal_iso = "";

        // ISSN
        $issn = "";

        // DOI
        $doi = "";

        $pattern = '/[0-9\.]+\/.*/';

        preg_match($pattern, $line[11], $match);

        if (count($match) > 0) {
          $doi = $match[0];
        }

        // Publication date
        $day = "";
        $month = "";
        $year = $line[2];

        array_push($articles, array("title" => $title, "abstract" => $abstract, "doi" => $doi, "journal_title" => $journal_title, "journal_iso" => $journal_iso, "journal_issn" => $issn, "day" => $day, "month" => $month, "year" => $year));
      }

      fclose($handle);
    }

    require_once("models/articles.php");
    
    $article_model = new Articles();

    foreach ($articles as $article) {
      $article_model->insert($db, $article, 'scopus');
    }
  }
}