<?php
require __DIR__ . "/vendor/autoload.php";
use Microsoft\PhpParser\{DiagnosticsProvider, Node, Parser, PositionUtilities};

$fuzzer->TEST_ROUTINE = function ($input){
    $parser = new Parser();
    $astNode = $parser->parseSourceFile($input);
};

function TEST($input){
	$parser = new Parser();
    	$astNode = $parser->parseSourceFile($input);
}


// php fuzz.php targets/php-parser/php-parser.php input10.txt targets/php-parser/vendor/microsoft/tolerant-php-parser/src/
?>
