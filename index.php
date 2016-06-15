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

  require_once("config.php");
  require_once("database.php");
  require_once("models/articles.php");

  $db = new Connector();

  $db->connect($config);

  $article_model = new Articles();

  // Get some counts for display
  $article_count = $article_model->count($db);
  $abstract_count = $article_model->count_not_empty($db);
?>

<html>
  <head>
    <title>Article parser</title>
  </head>
  <body style="padding: 0; margin: 0; background-color: #333;">
    <div style="padding: 25px; margin: 0 auto; width: 900px; background-color: white; height: 100%;">
      <p>
        <a href="init.php">Initialise the database</a>
      </p>
      <p>
        <a href="init.php?reinit=true">Re-initialise the database (removes all data)</a>
      </p>
      <p>
        <a href="compare.php">Remove duplicates from articles</a>
      </p>
      <p>
        <a href="csv.php">Get articles CSV</a>
      </p>
      <p>
        Articles in database: <?php echo $article_count; ?> </br>
        Articles with abstracts: <?php echo $abstract_count; ?>
      </p>
    </div>
  </body>
</html>