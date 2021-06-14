<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require_once __DIR__ . '/vendor/autoload.php';

use Michelf\Markdown;
use Michelf\MarkdownExtra;

$fuzzer->TEST_ROUTINE = function ($input){
    $result = Markdown::defaultTransform($input);
};

$fuzzer->addCustomMutationOperator(function(string $str) {
    $result = "** test **\n" . $str;
    return \strlen($result) <= $maxLen ? $result : null;
}, 'addEmphasis');

$fuzzer->addCustomMutationOperator(function(string $str) {
    $result = "> test\n" . $str;
    return \strlen($result) <= $maxLen ? $result : null;
}, 'addBlockQuotes');

