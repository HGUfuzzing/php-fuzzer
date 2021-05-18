<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require_once __DIR__ . '/vendor/autoload.php';

use Michelf\Markdown;
use Michelf\MarkdownExtra;

// $input = Markdown input
function TEST_ROUTINE($input) 
{
    $result = Markdown::defaultTransform($input);
}
?>