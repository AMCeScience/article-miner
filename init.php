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

error_reporting(E_ALL);

$time_start = microtime(true);

require_once('controllers/init.php');

$init = new Init();

$reinit = false;

if (isset($_GET) && isset($_GET["reinit"]) && $_GET["reinit"] == "true") {
  $reinit = true;
}

$init->run($reinit);

echo "Time elapsed: " . (microtime(true) - $time_start) . "s <br/>";

echo "<a href='/index.php'>Back to index page</a>";