<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require_once __DIR__ . '/vendor/autoload.php';

function TEST_ROUTINE($input){
    $oCssParser = new Sabberworm\CSS\Parser($input);
    $oCssDocument = $oCssParser->parse();
}
