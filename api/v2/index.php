<?php

header('Content-type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/api/v2/include/header.php');

if($_GET) {

  $arguments = $_GET;
  extract($_GET);

  $antweb = new antweb($pdo);

  if(isset($rank)) {
    $results = $antweb->getRank($rank);
  }
  if(isset($coord)) {
    $coord = $_REQUEST['coord'];
    $parts = explode(',',$coord);

    $lat = $parts[0];
    $lon = $parts[1];

    $r = 5;

    if(isset($_REQUEST['r'])) {
      $r = $_REQUEST['r'];
    }

    $results = $antweb->getCoord($lat,$lon,$r);

  }
  else {
    $results = $antweb->getSpecimens($arguments);
  }

  print $results;

}

else {
  header('Content-type: text/html');
  include('readme.html');
}

?>
