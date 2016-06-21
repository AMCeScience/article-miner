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

class WoS_parser {
  function run($config, $db) {
    require_once("libraries/run_check.php");
    
    $run_check = new Run_check();
    if ($run_check->should_run($config, $db, "wos")) {
      $this->parse($config, $db);

      $run_check->done_run($config, $db, "wos");
    }
  }

  function parse($config, $db) {
    ini_set("auto_detect_line_endings", true);

    $folder = $config["dir"] . $config["wos_dir"];

    if ($config["test"] === true) {
      $folder = $config["test_dir"] . $config["wos_dir"];
    }

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

      $articles = array();

      while (($line = fgets($handle)) !== false) {
        // Split on tabs
        $line = str_getcsv($line, "\t");

        // Check if this is the first line
        if ($line[1] === 'AU') {
          continue;
        }
        
        // Title
        $title = $line[9];

        // Abstract
        $abstract = $line[33];

        // Journal-Title
        $journal_title = $line[17];

        // Journal-ISO
        $journal_iso = "";

        // ISSN
        $issn = $line[50];

        // DOI
        $doi = "";

        $pattern = '/[0-9\.]+\/.*/';

        preg_match($pattern, $line[28], $match);

        if (count($match) > 0) {
          $doi = $match[0];
        }

        // Publication date
        $day = "";
        $month = "";
        $year = $line[32];

        array_push($articles, array("title" => $title, "abstract" => $abstract, "doi" => $doi, "journal_title" => $journal_title, "journal_iso" => $journal_iso, "journal_issn" => $issn, "day" => $day, "month" => $month, "year" => $year));
      }

      fclose($handle);
    }

    require_once("models/articles.php");
    
    $article_model = new Articles();

    foreach ($articles as $article) {
      $article_model->insert($db, $article, 'wos');
    }
  }
}