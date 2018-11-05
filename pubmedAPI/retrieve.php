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

include('PubMedAPI.php');
include('../config.php');
include('../models/articles.php');
include('../models/journals.php');
require_once('../database.php');
require_once('retrieve_ctrl.php');

$db = new Connector();

$db->connect($config);

$article_model = new Articles();
$journal_model = new Journals();

$pubMedAPI = new PubMedAPI();
$pubMedAPI->limit = 10000;
$pubMedAPI->show_urls = false;
$pubMedAPI->display_mode = false;
$pubMedAPI->return_mode = 'parsed';

$retriever = new PubMedArticleRetriever($db, $article_model, $journal_model, $pubMedAPI);
$retriever->run();

echo "<a href='/index.php'>Back to index page</a>";