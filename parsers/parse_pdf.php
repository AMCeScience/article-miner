<?php

require '../vendor/autoload.php';

use \Smalot\PdfParser\Parser;

class Parse_pdf {
  function __construct() {
    $parser = new Parser();

    $pdf = $parser->parseFile('../files/test.pdf');

    // Retrieve all pages from the pdf file.
    $pages  = $pdf->getPages();

    // Loop over each page to extract text.
    foreach ($pages as $page) {
        var_dump(preg_split("/\r\n|\n|\r/", $page->getText()));
        
        exit;
    }
  }

  function clean_page_endings($page) {
    
  }

  function split_page($page) {
    // http://stackoverflow.com/a/11165332/6469606
    return preg_split("/\r\n|\n|\r/", $page);
  }
}

new Parse_pdf();