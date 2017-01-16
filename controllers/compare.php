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

class Compare {
  function __construct($db = null) {
    include('config.php');

    $this->config = $config;

    $this->db = $db;

    if ($db === null) {
      require_once('database.php');

      $this->db = new Connector();

      $this->db->connect($this->config);
    }
  }

  function remove_empty_and_doubles($remove_from_specific_db = false) {
    require_once("models/articles.php");

    $article_model = new Articles();

    $abstracts_deleted = $article_model->delete_empty_abstracts($this->db);
    $titles_deleted = $article_model->delete_double_title($this->db, $remove_from_specific_db);
    $dois_deleted = $article_model->delete_double_doi($this->db, $remove_from_specific_db);

    return [$abstracts_deleted, $titles_deleted, $dois_deleted];
  }
}