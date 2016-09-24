<?php

use ksr\classes\FTPHandler;
use ksr\classes\SerialRenaimer;

include __DIR__ . '/autoload.php';

include __DIR__ . '/vendor/autoload.php';


try {
    $renamer = new SerialRenaimer(new FTPHandler());
    $renamer->run();
} catch (Exception $ex) {
    echo $ex->getMessage();
}