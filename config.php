<?php

$config["journal_list_reinit"] = false;
$config["journal_list_dir"] = "../../Downloads/journal_categories.csv";

$config["pubmed_central_list_reinit"] = false;
$config["pubmed_central_dir"] = "../../Downloads/pmc_result.xml";

$config["database"]["ip"] = "localhost";
$config["database"]["username"] = "root";
$config["database"]["password"] = "root";
$config["database"]["schema"] = "mine";

$config["alchemy_key_dir"] = "/Users/Allard/workspace/miner/alchemyAPI/";
$config["alchemy_reinit"] = false;
$config["alchemy_transactions"] = 1000;
$config["alchemy_collect"] = array("taxonomy", "keywords", "entities");

if (isset($_GET) && isset($_GET["reinit"]) && $_GET["reinit"] == "true") {
  $config["journal_list_reinit"] = true;
  $config["pubmed_central_list_reinit"] = true;
}
?>