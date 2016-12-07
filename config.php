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

// _run: should this parser be executed during a normal run
// _reinit: if the parser has run before, should the database be cleared and the parser run again
// _dir: location of the input files for the parser

$config["journal_list_run"] = true;
$config["journal_list_reinit"] = true;
$config["journal_list_dir"] = "files/journal_categories.csv";

$config["pubmed_central_run"] = true;
$config["pubmed_central_list_reinit"] = true;
$config["pubmed_central_dir"] = "pubmed central";

$config["pubmed_run"] = true;
$config["pubmed_list_reinit"] = true;
$config["pubmed_dir"] = "pubmed";

$config["ovid_run"] = false;
$config["ovid_list_reinit"] = false;
$config["ovid_dir"] = "ovid";

$config["wos_run"] = false;
$config["wos_list_reinit"] = false;
$config["wos_dir"] = "web of science";

$config["scopus_run"] = false;
$config["scopus_list_reinit"] = false;
$config["scopus_dir"] = "scopus";

// File dir, appended to every _dir listed above
$config["dir"] = "files/contrast/";
// File dir for test mode, appended to every _dir listed above
$config["test_dir"] = "files/test folder/";

// Excludes for output
$config['exclude_journal_array'] = array(
	'2167-6461' => 'Proceedings : ... IEEE International Conference on Big Data. IEEE International Conference on Big Data',
	'1932-6203' => 'PLoS ONE',
	'1424-8220' => 'Sensors (Basel, Switzerland)',
	'0036-8075' => 'GigaScience',
	'2045-2322' => 'Scientific Reports',
	'1873-1457' => 'Physics of life reviews',
	'1879-2782' => 'Neural networks : the official journal of the International Neural Network Society',
	'2356-6140' => 'The Scientific World Journal',
	'1749-6632' => 'Annals of the New York Academy of Sciences',
	'2168-2275' => 'IEEE transactions on cybernetics',
	'2162-2388' => 'IEEE transactions on neural networks and learning systems',
	'1364-503X' => 'Philosophical transactions. Series A, Mathematical, physical, and engineering sciences',
	'0272-1716' => 'IEEE computer graphics and applications',
	'0162-1459' => 'Journal of the American Statistical Association',
	'1549-960X' => 'Journal of chemical information and modeling',
	'0036-8733' => 'Scientific American',
	'1361-6528' => 'Nanotechnology',
	'1941-0042' => 'IEEE transactions on image processing : a publication of the IEEE Signal Processing Society',
	'1079-7114' => 'Physical review letters',
	'1941-0506' => 'IEEE transactions on visualization and computer graphics',
	'1878-4372' => 'Trends in plant science',
	'1361-6609' => 'Public understanding of science (Bristol, England)',
	'2045-7758' => 'Ecology and Evolution',
	'0017-8012' => 'Harvard business review',
	'1879-1026' => 'The Science of the total environment',
	'1939-1854' => 'The Journal of applied psychology',
	'empty1' => 'SpringerPlus',
	'empty2' => 'Frontiers in Psychology',
	'empty3' => 'PeerJ',
	'empty4' => 'Journal of Big Data'
);
$config['exclude_journal_id'] = array();

// Set test mode
$config["test"] = false;

$config["database"]["ip"] = "localhost";
$config["database"]["username"] = "root";
$config["database"]["password"] = "root";
$config["database"]["schema"] = "miner";

if (isset($_GET) && isset($_GET["reinit"]) && $_GET["reinit"] == "true") {
  $config["journal_list_reinit"] = true;
  $config["pubmed_central_list_reinit"] = true;
  $config["ovid_list_reinit"] = true;
  $config["pubmed_list_reinit"] = true;
  $config["wos_list_reinit"] = true;
  $config["scopus_list_reinit"] = true;
}