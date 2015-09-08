<?php

class ScriptChecker {
  function has_ran($db, $name) {
    $result = $this->find($db, $name);

    if ($result) {
      return (boolean) $result["ran"];
    }

    return false;
  }

  function find($db, $name) {
    $sql = "SELECT * FROM Scripts_ran WHERE name LIKE '%$name%'";

    $result = $db->query($sql);

    if ($result) {
      return $result->fetch_array();
    }

    return false;
  }

  function set($db, $name, $value = true) {
    $result = $this->find($db, $name);

    $value = (int) $value;

    if ($result) {
      $db->query("UPDATE Scripts_ran SET ran = '$value' WHERE name = '$name'");
    } else {
      $db->query("INSERT INTO Scripts_ran (name, ran) VALUES ('$name', $value)");
    }
  }
}

class AlchemyTransactions {
  function find_today($db) {
    $result = $db->query("SELECT * FROM Alchemy_transactions WHERE date > NOW() - INTERVAL 1 DAY");

    if ($result) {
      return $result->fetch_array();
    }

    return false;
  }

  function insert($db, $amount = 0) {
    if ($this->find_today($db) == false) {
      $db->query("INSERT INTO Alchemy_transactions (transactions_used) VALUES ($amount)");

      return true;
    } else {
      return false;
    }
  }

  function used_today($db) {
    $result = $this->find_today($db);

    if ($result) {
      return $result["transactions_used"] * 1;
    }

    $this->insert($db);

    return 0;
  }

  function update_today($db, $amount) {
    $now = $this->find_today($db);

    if ($now) {
      $db->query("UPDATE Alchemy_transactions SET transactions_used = transactions_used + $amount WHERE id = {$now["id"]}");
    }

    $this->insert($db, $amount);
  }
}

?>