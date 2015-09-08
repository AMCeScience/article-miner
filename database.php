<?php

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

?>