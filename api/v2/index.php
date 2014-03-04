<?php

header('Content-type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/api/v2/include/header.php');

if($_GET) {

  $arguments = $_GET;

  $antweb = new antweb($pdo);

  $specimens = $antweb->getSpecimens($arguments);

  print $specimens;

}

else {
  header('Content-type: text/html');
  include('readme.html');
}

?>
