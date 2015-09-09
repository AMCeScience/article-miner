<html>
  <head></head>
  <body>
    <?php
      require_once("config.php");
      require_once("database.php");

      $db = new Connector();

      $db->connect($config);

      require_once("models/alchemy_outcomes.php");

      $outcome_model = new AlchemyOutcomes();

      $results = $outcome_model->statistics($db);
    ?>

    <h3>Keywords</h3>
    <ul>
      <?php
        while ($result = $results["keywords"]->fetch_array()) {
          echo "<li>{$result["text"]}, {$result["count"]}</li>";
        }
      ?>
    </ul>

    <h3>Entities</h3>
    <ul>
      <?php
        while ($result = $results["entities"]->fetch_array()) {
          echo "<li>{$result["text"]}, {$result["type"]}, {$result["count"]}</li>";
        }
      ?>
    </ul>

    <h3>Concepts</h3>
    <ul>
      <?php
        while ($result = $results["concepts"]->fetch_array()) {
          echo "<li>{$result["text"]}, {$result["count"]}</li>";
        }
      ?>
    </ul>

    <h3>Taxonomy</h3>
    <ul>
      <?php
        while ($result = $results["taxonomy"]->fetch_array()) {
          echo "<li>{$result["label"]}, {$result["count"]}</li>";
        }
      ?>
    </ul>
  </body>
</html>