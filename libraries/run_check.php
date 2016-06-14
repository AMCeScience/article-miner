<?php

class Run_check {
  // Check whether we want to run the script identified by 'script_name'
  function should_run($config, $db, $script_name) {
    require_once("models/articles.php");
    require_once("models/scriptchecker.php");

    $article_model = new Articles();
    $script_model = new ScriptChecker();

    $reinit_string = $script_name . "_list_reinit";

    // Check whether this is a re-initialisation of the DB, or the DB is empty, or the script has not run yet
    if ($config[$reinit_string] || !$article_model->is_filled($db) || !$script_model->has_ran($db, $script_name)) {
      // Run the script (again)
      echo "Reinitializing the {$script_name} list... ";
      
      return true;
    } else {
      // Do not run the script
      echo "Skipping {$script_name} list initialisation.<br/>";
    }

    return false;
  }

  // Update Scripts_ran table for script identified by 'script_name'
  function done_run($config, $db, $script_name) {
    require_once("models/scriptchecker.php");

    $script_model = new ScriptChecker();

    $script_model->set($db, $script_name, true);

    echo "done.<br/>";
  }
}