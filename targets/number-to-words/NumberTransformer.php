<?php 
require_once __DIR__ . '/vendor/autoload.php';

use NumberToWords\NumberToWords; 

$_input = '';

function TEST_ROUTINE($S) {
    global $_input;
    $_input = $S;
    $numException = 0;

    try {
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer(_split(2));
        $numberTransformer->toWords(1234567890112);
    } catch(InvalidArgumentException $e) {
        ++$numException;
    }
}



function _split($len) {
    global $_input;
    $ret = substr($_input, 0, $len);
    $_input = substr($_input, $len);
    return $ret;
}