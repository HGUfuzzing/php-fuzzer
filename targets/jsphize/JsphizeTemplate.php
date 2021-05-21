<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require_once __DIR__ . '/vendor/autoload.php';
use JsPhpize\JsPhpize;

$fuzzer->TEST_ROUTINE = function ($input){
    $jsPhpize = new JsPhpize();
    $result = $jsPhpize->renderCode($input);
};

