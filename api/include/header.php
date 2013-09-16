<?php

//require $_SERVER['DOCUMENT_ROOT'] . '/include/php/config.php';

$pdo = new PDO('mysql:dbname=antweb','root','root');

//Autoloaded instantiated to load objects in defined paths
require $_SERVER['DOCUMENT_ROOT'] . "/api/include/class/autoloader/Autoloader.php";

//Load trainit Class 
$autoloader = new Autoloader($_SERVER['DOCUMENT_ROOT'] . '/api/include/antweb/');
$autoloader->register();

session_start();

?>