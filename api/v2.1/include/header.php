<?php

//require $_SERVER['DOCUMENT_ROOT'] . '/include/php/config.php';

if(preg_match('/local/i', $_SERVER['HTTP_HOST'])) {
  $pdo = new PDO('mysql:dbname=antweb','root','root');
}
else {
  $pdo = new PDO('mysql:dbname=ant','antweb','f0rm1c6');
}

include($_SERVER['DOCUMENT_ROOT'] . '/api/v1/include/antweb/antweb.php');

//session_start();

?>
