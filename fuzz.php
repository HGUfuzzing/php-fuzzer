<?php

//include all autoload files.
require_once __DIR__ . '/vendor/autoload.php';

use Fuzzer\Fuzzer;

$fuzzer = new Fuzzer($argv);
$fuzzer->init();
$fuzzer->run();