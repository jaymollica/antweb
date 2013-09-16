#!/usr/bin/php
<?php

try {
    include __DIR__ . '/../../Autoloader.php';

    $a = new ClassA();
    $b = new ClassB();

    var_dump($a, $b);

} catch (ExceptionB $e) {

}
