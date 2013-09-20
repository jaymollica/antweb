<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/api/include/header.php');

if($_REQUEST) {

  $antweb = new antweb($pdo);

  if(isset($_REQUEST['rank'])) {
    $rank = $_REQUEST['rank'];

    if(isset($_REQUEST['name'])) {
      $name = $_REQUEST['name'];
    }
    else {
      $name = NULL;
    }

    $specimens = $antweb->getSpecimens($rank,$name);

    print '<pre>'; print_r($specimens); print '</pre>';

  }
  elseif(isset($_REQUEST['code'])) {
    $code = $_REQUEST['code'];
    $specimen = $antweb->getSpecific($code);

    print '<pre>'; print_r($specimen); print '</pre>';
  }
  elseif(isset($_REQUEST['coord'])) {
    $coord = $_REQUEST['coord'];
    $parts = explode(',',$coord);

    $lat = $parts[0];
    $lon = $parts[1];

    $r = 25;

    if(isset($_REQUEST['r'])) {
      $r = $_REQUEST['r'];
    }

    $specimens = $antweb->getCoord($lat,$lon,$r);

    print '<pre>'; print_r($specimens); print '</pre>';

  }
  elseif(isset($_REQUEST['since'])) {

    $days = $_REQUEST['since'];

    if(isset($_REQUEST['img'])) {

      $type = NULL;
      if(isset($_REQUEST['type'])) {
        $type = $_REQUEST['type'];
      }

      $specimens = $antweb->getImagesAddedAfter($days,$type);

    }
    else {
      $specimens = $antweb->getSpecimensCreatedAfter($days);
    }

    print '<pre>'; print_r($specimens); print '</pre>';

  }
  else {
    include('readme.html');
  }

}

?>