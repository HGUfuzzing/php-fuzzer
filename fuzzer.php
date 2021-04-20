<?php
require_once __DIR__ . '/vendor/autoload.php';
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover as CloverReport;
use SebastianBergmann\CodeCoverage\Report\Text as TextReport;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;


if(!isset($argv[1]) || !isset($argv[2])) 
    die('usage : php ' . pathinfo(__FILE__, PATHINFO_BASENAME) . ' <target_file> <source1>[, <source2>, <source3>, ... ]');

$target_file_path = $argv[1];
$sources = [];

for($i = 2; $i < count($argv); $i++) {
    if(! file_exists($argv[$i])) die('"' .  $argv[$i] . '" is not exist.');
    $sources[] = $argv[$i];
}

if(! file_exists($target_file_path)) {
    die('The target file is not exist.');
}

require $target_file_path;

$filter = new Filter;

/* 
    Problem : 
        1. includeDirectory method에서 Command line argument로 받은 한글 path를 인식 못하는 문제 있음.
*/
foreach($sources as $source) {
    $filter->includeDirectory($source);
    echo "<include> : " . $source . "\n";
}

//setup
$coverage_obj = new CodeCoverage(
    (new Selector)->forLineCoverage($filter),
    $filter
);

$reps = 100000;
for($i = 0; $i < $reps; $i++) {
    try {
        
        $coverage_obj->start(__FILE__);
        TEST_ROUTINE(random_string(200));
        $coverage_obj->stop();
        
        $coverage = get_coverage_from_coverage_obj($coverage_obj);
        $acc = get_acc_from_coverage($coverage);
        print_r($acc . ' ');
    } catch(Exception $e) {
        echo 'Exception Message : ' . $e->getMessage();
        return;
    }
}


function get_coverage_from_coverage_obj(SebastianBergmann\CodeCoverage\CodeCoverage $coverage_obj) {
    $report = $coverage_obj->getReport();
    static $coverage = [];

    $file_num = 0;

    foreach ($report as $item) {
        if (!$item instanceof SebastianBergmann\CodeCoverage\Node\File) {
            continue;
        }

        $coverageData = $item->lineCoverageData();

        foreach ($coverageData as $line => $data) {
            if ($data === null) {
                continue;
            }
            
            if(!isset($coverage[$file_num][$line]))
                $coverage[$file_num][$line] = count($data);
            else
                $coverage[$file_num][$line] += count($data);
        }
        $file_num += 1;
    }
    return $coverage;
}


function get_acc_from_coverage($coverage) {
    $acc = 0;
    foreach($coverage as $file) {
        foreach($file as $line => $cnt) {
            if($cnt)
                $acc += 1;
        }
    }
    return $acc;
}


function random_string($len = 100) {
    $str = '';
    for($i = 0; $i < $len; $i++) {
        $ascii_code = mt_rand(25, 126);
        if($ascii_code < 28)
            $str .= '\n';
        else if($ascii_code < 32)
            $str .= ' ';  
        else
            $str .= chr($ascii_code);   
    }
    return $str;
}