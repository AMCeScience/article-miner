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
      if ($config[$reinit_string] && !$article_model->is_filled($db)) {
        $article_model->delete_by_db($db, $script_name);
      }

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