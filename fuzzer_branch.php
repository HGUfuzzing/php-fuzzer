<?php
require_once __DIR__ . '/vendor/autoload.php';
require './Mutator.php';

use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\ProcessedCodeCoverageData;
use SebastianBergmann\CodeCoverage\Report\Clover as CloverReport;
use SebastianBergmann\CodeCoverage\Report\Text as TextReport;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;


// php fuzzer_branch.php  targets/jsphize/JsphizeTemplate.php input.txt ./targets/jsphize/vendor/js-phpize/js-phpize/src/
if(!isset($argv[1]) || !isset($argv[2]) || !isset($argv[3])) 
    die('usage : php ' . pathinfo(__FILE__, PATHINFO_BASENAME) . ' <target_file> <input_file> <source1>[, <source2>, <source3>, ... ]');

$target_file_path = $argv[1];
$input_file = $argv[2];
$sources = [];

for($i = 3; $i < count($argv); $i++) {
    if(! file_exists($argv[$i])) die('"' .  $argv[$i] . '" is not exist.');
    $sources[] = $argv[$i];
}
if(! file_exists($target_file_path)) die('The target file is not exist.');
if(! file_exists($input_file)) die('The input file is not exist.');

echo "<target file> : $target_file_path \n";
echo "<input file> : $input_file\n";

require $target_file_path;

$initial_input = read_text_from_file($input_file);


$filter = new Filter;

foreach($sources as $source) {
    $filter->includeDirectory($source);
    echo "<source> : " . $source . "\n";
}

$coverage_obj = new CodeCoverage(
    (new Selector)->forLineAndPathCoverage($filter),
    $filter
);

$queue1 = [$initial_input];
$queue2 = [];

$prev_branch = TEST($initial_input);

for($tc = 0; ;$tc++) {
    try {
        if($tc % 100 == 0)
            gc_collect_cycles();
        
        $idx = rand(0, count($queue1)-1);
        $prev_input = $queue1[$idx];
        
        $cur_input = mutate($prev_input);
        $cur_branch = TEST($cur_input);
        if(has_branch_difference($prev_branch, $cur_branch)) {
            $queue1[] = $cur_input;
            echo "\nInteresting finded!\n";
        }

        echo get_branch_acc_from_branch_coverage($cur_branch) . ' ';

        unset($prev_branch);
        $prev_branch = $cur_branch;

    }
    catch(Exception $e) {
        echo 'Exception Message : ' . $e->getMessage();
    } catch(Error $e) {
        echo 'Error Message : ' . $e->getMessage();
    }
}


function get_branch_acc_from_branch_coverage($branch_coverage) {
    $acc = 0;
    foreach ($branch_coverage as $file => $functions) {
        foreach ($functions as $functionName => $functionData) {
            foreach ($functionData['branches'] as $branchId => $branchData) {
                if($branchData) 
                    $acc += 1;
            }
        }
    }
    return $acc;
}

function has_branch_difference($prev_branch, $cur_branch) {
    foreach ($cur_branch as $file => $functions) {
        foreach ($functions as $functionName => $functionData) {
            foreach ($functionData['branches'] as $branchId => $branchCount) {
                if(!isset($prev_branch[$file][$functionName]['branches'][$branchId]) || 
                    $prev_branch[$file][$functionName]['branches'][$branchId] < $branchCount) 
                {
                    return true;
                }
            }
        }
    }
}


function mutate($input) {
    $mut_obj = new Mutator($input);

    $num = strlen($input) / 12;

    for($i = 0; $i < $num; $i++) $mut_obj->mutate();

    return $mut_obj->getInput();
}


function read_text_from_file($file) {
    $fp = fopen($file, "r");

    $text = '';
    while( !feof($fp) ) {
        $text .= fgets($fp);
    }

    fclose($fp);

    return $text;
}

function get_branch_coverage_from_coverage_obj(CodeCoverage $coverage_obj) {
    $branch = [];
    $coverage_data = $coverage_obj->getData();
    $functionCoverage = $coverage_data->functionCoverage();

    foreach ($functionCoverage as $file => $functions) {
        foreach ($functions as $functionName => $functionData) {
            foreach ($functionData['paths'] as $pathId => $pathData) {
                if ( (bool) $pathData['hit']) {
                    foreach ($pathData['path'] as $id => $branchId) {
                        if(isset($branch[$file][$functionName]['branches'][$branchId]))
                            $branch[$file][$functionName]['branches'][$branchId] += 1;
                        else
                            $branch[$file][$functionName]['branches'][$branchId] = 1;
                    }
                }
            }
        }
    }
    return $branch;
}

function TEST(string $input) {
    global $coverage_obj, $target_file_path;

    $coverage_obj->start($target_file_path, false);
    TEST_ROUTINE($input);
    $coverage_obj->stop();
    
    $branch_coverage = get_branch_coverage_from_coverage_obj($coverage_obj);
    
    return $branch_coverage;
}