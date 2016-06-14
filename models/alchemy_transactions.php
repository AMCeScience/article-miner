<?php

class AlchemyTransactions {
  function row_exists_for_today($db) {
    $result = $db->query("SELECT id FROM Alchemy_transactions WHERE DATE(date) = CURDATE()");

    if ($result) {
      return true;
    }

    return false;
  }

  function insert($db, $transactions_used) {
    if (!$this->row_exists_for_today($db)) {
      $db->query("INSERT INTO Alchemy_transactions (transactions_used) VALUES ('$transactions_used')");
    }
  }

  function used_today($db) {
    $result = $db->query("SELECT transactions_used FROM Alchemy_transactions WHERE DATE(date) = CURDATE()");

    if ($result) {
      return $result->fetch_array()['transactions_used'];
    }

    return 0;
  }

  function update_today($db, $transactions_used) {
    $this->insert($db, 0);

    $db->query("UPDATE Alchemy_transactions SET transactions_used = transactions_used + $transactions_used WHERE DATE(date) = CURDATE()");
  }

  function clear($db) {
    // Clear table
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    $db->query("TRUNCATE TABLE Alchemy_transactions");
    $db->query("SET FOREIGN_KEY_CHECKS = 1");
  }
}