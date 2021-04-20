<?php 
// error_reporting(E_ALL^ E_WARNING);
require_once __DIR__ . '/vendor/autoload.php';

//for target project
use Smalot\PdfParser\Document;
use Smalot\PdfParser\PDFObject; 


$_input = '';


function TEST_ROUTINE($S) {
    global $_input;
    $_input = $S;
        
    $offset = 20;

    //Test getCommandsText
    $parts = (new PDFObject(new Document()))->getCommandsText(_split(6000), $offset);

    //Test cleanContent
    $cleaned = (new PDFObject(new Document()))->cleanContent(_split(6000), _split(1));

    //Test getSectionText
    $sections = (new PDFObject(new Document()))->getSectionsText(_split(6000));
}

function _split($len) {
    global $_input;
    $ret = substr($_input, 0, $len);
    $_input = substr($_input, $len);
    return $ret;
}