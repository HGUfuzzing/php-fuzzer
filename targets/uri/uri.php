<?php error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require __DIR__ . '/vendor/autoload.php';
$fuzzer->TEST_ROUTINE = function ($input){
    \League\Uri\Uri::createFromString($input);
};

