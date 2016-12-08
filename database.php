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

class Connector {
  function connect($config) {
    $this->connection = new mysqli($config['database']['ip'],
               $config['database']['username'], 
               $config['database']['password'], 
               $config['database']['schema']);

    if (mysqli_connect_errno()) {
      printf("Connect failed: %s\n", mysqli_connect_error());
      
      return false;
    }
    
    // Force connection to UTF-8
    $this->query("SET NAMES utf8;");
        
    return $this->connection;
  }

  function query($sql) {
    $result = $this->connection->query($sql);

    if (stripos($sql, 'select') !== false && isset($result->num_rows) && $result->num_rows < 1) {
      return false;
    }

    if (!$result) {
      echo $this->connection->error;
    }
    
    return $result;
  }
}