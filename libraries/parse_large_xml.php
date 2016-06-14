<?php

class XML_parser {
  /**
   * For every node that starts with $startNode and ends with $endNode call $callback
   * with the string as an argument
   *
   * Note: Sometimes it returns two nodes instead of a single one, this could easily be
   * handled by the callback though. This function primary job is to split a large file
   * into manageable XML nodes.
   *
   * the callback will receive one parameter, the XML node(s) as a string
   *
   * @param resource $handle - a file handle
   * @param string $startNode - what is the start node name e.g <item>
   * @param string $endNode - what is the end node name e.g </item>
   * @param callable $callback - an anonymous function
   */
  function nodeStringFromXMLFile($handle, $startNode, $endNode, $db, $callback=null) {
      $cursorPos = 0;
      $articles = array();

      while(true) {
        // Find start position
        $startPos = $this->getPos($handle, $startNode, $cursorPos);

        // We reached the end of the file or an error
        if($startPos === false) { 
          break;
        }

        // Find where the node ends
        $endPos = $this->getPos($handle, $endNode, $startPos) + mb_strlen($endNode);

        // Jump back to the start position
        fseek($handle, $startPos);

        // Read the data
        $data = fread($handle, ($endPos-$startPos));

        // pass the $data into the callback
        array_push($articles, $callback($this, $db, $data));

        // next iteration starts reading from here
        $cursorPos = ftell($handle);
      }

      return $articles;
  }

  /**
   * This function will return the first string it could find in a resource that matches the $string.
   *
   * By using a $startFrom it recurses and seeks $chunk bytes at a time to avoid reading the 
   * whole file at once.
   * 
   * @param resource $handle - typically a file handle
   * @param string $string - what string to search for
   * @param int $startFrom - strpos to start searching from
   * @param int $chunk - chunk to read before rereading again
   * @return int|bool - Will return false if there are EOL or errors
   */
  function getPos($handle, $string, $startFrom=0, $chunk=1024, $prev='') {
    // Set the file cursor on the startFrom position
    fseek($handle, $startFrom, SEEK_SET);

    // Read data
    $data = fread($handle, $chunk);

    // Try to find the search $string in this chunk
    $stringPos = mb_strpos($prev.$data, $string);

    // We found the string, return the position
    if($stringPos !== false ) {
      return $stringPos+$startFrom - mb_strlen($prev);
    }

    // We reached the end of the file
    if(feof($handle)) {
      return false;
    }

    // Recurse to read more data until we find the search $string it or run out of disk
    return $this->getPos($handle, $string, $chunk+$startFrom, $chunk, $data);
  }
}