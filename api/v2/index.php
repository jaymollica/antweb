<?php

header('Content-type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/api/v2/include/header.php');

if($_GET) {

  $arguments = $_GET;
  extract($_GET);

  $antweb = new antweb($pdo);

  if(isset($rank)) {

    $results = $antweb->getRank($rank,$limit,$offset);
  }
  elseif(isset($coord)) {
    $coord = $_REQUEST['coord'];
    $parts = explode(',',$coord);

    $lat = $parts[0];
    $lon = $parts[1];

    if(!isset($r)) { $r = 5; }

    if(!isset($limit)) { $limit = FALSE; }
    if(!isset($offset)) {$offset = FALSE; }

    $results = $antweb->getCoord($lat,$lon,$r,$limit,$offset);

  }
  elseif(isset($since)) {

    $days = $since;

    $specimens = $antweb->getImagesAddedAfter($days,$img_type);

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
