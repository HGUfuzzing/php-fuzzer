<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require_once __DIR__ . '/vendor/autoload.php';

use Michelf\Markdown;
use Michelf\MarkdownExtra;

$fuzzer->TEST_ROUTINE = function ($input){
    $result = Markdown::defaultTransform($input);
};

$fuzzer->addCustomMutationOperator(function(string $str, int $maxLen) {
    $result = "** test **\n" . $str;
    // echo "addEmphasis\n";
    return \strlen($result) <= $maxLen ? $result : null;
}, 'addEmphasis');

$fuzzer->addCustomMutationOperator(function(string $str, int $maxLen) {
    $result = "> test\n" . $str;
    // echo "addBlockQuotes\n";
    return \strlen($result) <= $maxLen ? $result : null;
}, 'addBlockQuotes');

