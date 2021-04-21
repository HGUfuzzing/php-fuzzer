<?php
require_once __DIR__ . '/vendor/autoload.php';
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover as CloverReport;
use SebastianBergmann\CodeCoverage\Report\Text as TextReport;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;


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

/* 
    Problem : 
        includeDirectory method에서 Command line argument로 받은 한글 path를 인식 못하는 문제 있음.
*/
foreach($sources as $source) {
    $filter->includeDirectory($source);
    echo "<source> : " . $source . "\n";
}

$coverage_obj = new CodeCoverage(
    (new Selector)->forLineCoverage($filter),
    $filter
);

$queue1 = [$initial_input];
$queue2 = [];

$prev_coverage = TEST($initial_input);

while(1) {
    try {
        if(count($queue1) >= 1) $prev_input = array_pop($queue1);
        else $prev_input = array_pop($queue2);
        
        $cur_input = mutate($prev_input);
        $cur_coverage = TEST($cur_input);
        if(has_difference($prev_coverage, $cur_coverage)) {
            $queue1[] = $cur_input;
            echo "\nInteresting finded!\n";
            echo "$cur_input\n";
        }
        else {
            $queue2[] = $cur_input;
        }
        $prev_coverage = $cur_coverage;
        // echo count($queue1) . ', ' . count($queue2) . "\n";
        $acc = get_acc_from_coverage($cur_coverage);
        echo ("$acc ");
    } catch(Exception $e) {
        echo 'Exception Message : ' . $e->getMessage();
        return;
    }
}


function get_coverage_from_coverage_obj(SebastianBergmann\CodeCoverage\CodeCoverage $coverage_obj) {
    $report = $coverage_obj->getReport();
    $coverage = [];

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

function has_difference($prev_coverage, $cur_coverage) {
    foreach($cur_coverage as $file_num => $file) {
        foreach($file as $line => $cnt) {
            if(!isset($prev_coverage[$file_num][$line])) {
                // echo "\nfile num/line : $file_num/$line\n";
                return true;
            }
        }
    }
    return false;
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


function random_string($len = 1000) {
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

function mutate($input) {
    return random_string();
}


function read_text_from_file($file) {
    $fp = fopen($file, "r");

    $text = '';
    while( !feof($fp) ) {
        $text .= fgets($fp);
    }

    // 파일 닫기
    fclose($fp);

    return $text;
}

function TEST(string $input) {
    global $coverage_obj, $target_file_path;

    $coverage_obj->start($target_file_path, true);
    TEST_ROUTINE($input);
    $coverage_obj->stop();
    
    $coverage = get_coverage_from_coverage_obj($coverage_obj);
    return $coverage;
}