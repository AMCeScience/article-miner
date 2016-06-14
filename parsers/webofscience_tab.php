<?php

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

    $articles = array();

    foreach($scanned_directory as $file) {
      // Open the file
      if (($handle = @fopen($folder . "/" . $file, "r")) === false) {
        echo "Error: file not found at: " . $folder . "/" . $file;

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