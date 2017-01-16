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