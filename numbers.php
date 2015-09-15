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

      $taxonomy_results = $outcome_model->taxonomy_outcomes_by_level($db);

      $taxonomy_tree = array();

      $a = 0;

      while ($result = $taxonomy_results->fetch_array()) {
        $branches = explode("/", $result["label"]);

        $taxonomy_tree = add($taxonomy_tree, $branches, $result["count"]);
      }

      print_tree($taxonomy_tree);

      function print_tree($root, $level = 0) {
        // var_dump($root);

        if (count($root) > 1 || !array_key_exists("count", $root)) {
          foreach($root as $name => $branch) {
            if ($name == "count") {
              continue;
            }

            for($i = 0; $i < $level; $i++) {
              echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            }

            $count = "";

            if (array_key_exists("count", $branch)) {
              $count = " (" . $branch["count"] . ")";
            }

            echo $name . $count . "<br/>";

            if (count($branch) > 0) {
              print_tree($branch, $level + 1);
            }
          }
        }
      }

      function add($root, $branches, $count, $depth = 0) {
        if ($depth == count($branches)) {
          return array("count" => $count);
        }

        if (!array_key_exists($branches[$depth], $root)) {
          $root[$branches[$depth]] = array();

          $root[$branches[$depth]] = add($root[$branches[$depth]], $branches, $count, $depth + 1);

          return $root;
        } else {
          $root[$branches[$depth]] = add($root[$branches[$depth]], $branches, $count, $depth + 1);

          return $root;
        }
      }
    ?>
  </body>
</html>