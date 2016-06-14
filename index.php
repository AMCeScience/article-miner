<?php
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