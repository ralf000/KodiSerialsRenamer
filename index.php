<?php
header("Content-Type: text/html; charset=utf-8");

use ksr\classes\FTPHandler;
use ksr\classes\KodiSerialRenamer;
use ksr\exceptions\BaseException;

include __DIR__ . '/autoload.php';

include __DIR__ . '/vendor/autoload.php';


try {
    $renamer = new KodiSerialRenamer(new FTPHandler());
    $renamer->run();
} catch (BaseException $ex) {
    echo $ex->getMessage();
}