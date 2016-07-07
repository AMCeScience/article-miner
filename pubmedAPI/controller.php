<?php

include('PubMedAPI.php');
$search_term = 'big data[TIAB]';
$pubMedAPI = new PubMedAPI();
$pubMedAPI->display_mode = false;
// $pubMedAPI->data_type = 'json';
$results = $pubMedAPI->search($search_term);

foreach($results->articles as $r) {
  echo $r->abstract . '<br/><br/>';
}