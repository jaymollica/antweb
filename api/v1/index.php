<?php

header('Content-type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/api/v1/include/header.php');

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

    print $specimens;

  }
  elseif(isset($_REQUEST['species']) && isset($_REQUEST['genus'])) {
    $species = $_REQUEST['species'];
    $genus = $_REQUEST['genus'];

    $specimens = $antweb->getSpecies($genus,$species);

    print $specimens;

  }
  elseif(isset($_REQUEST['species']) && !isset($_REQUEST['genus'])) {
    $rank = 'species';
    $name = $_REQUEST['species'];

    $specimens = $antweb->getSpecimens($rank,$name);

    print $specimens;

  }
  elseif(!isset($_REQUEST['species']) && isset($_REQUEST['genus'])) {
    $rank = 'genus';
    $name = $_REQUEST['genus'];

    $specimens = $antweb->getSpecimens($rank,$name);

    print $specimens;

  }
  elseif(isset($_REQUEST['code'])) {
    $code = $_REQUEST['code'];
    $specimen = $antweb->getSpecific($code);

    print $specimen;

  }
  elseif(isset($_REQUEST['coord'])) {
    $coord = $_REQUEST['coord'];
    $parts = explode(',',$coord);

    $lat = $parts[0];
    $lon = $parts[1];

    $r = 5;

    if(isset($_REQUEST['r'])) {
      $r = $_REQUEST['r'];
    }

    $specimens = $antweb->getCoord($lat,$lon,$r);

    print $specimens;

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

    print $specimens;

  }
  else {
    header('Content-type: text/html');
    include('readme.html');
  }

}
else {
  header('Content-type: text/html');
  include('readme.html');
}

?>
