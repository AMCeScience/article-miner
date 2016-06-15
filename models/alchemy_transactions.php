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