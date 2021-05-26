<?php
require __DIR__ . "/vendor/autoload.php";
use Microsoft\PhpParser\{DiagnosticsProvider, Node, Parser, PositionUtilities};

$fuzzer->TEST_ROUTINE = function ($input){
    $parser = new Parser();
    $astNode = $parser->parseSourceFile($input);
};
?>
