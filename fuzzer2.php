<?php
if(!isset($argv[1]) || !isset($argv[2])) 
    die('usage : php ' . pathinfo(__FILE__, PATHINFO_BASENAME) . ' <target file> <reps>');
$target_file_path = $argv[1];
$reps = (int)$argv[2];

require_once $target_file_path;

$path = pathinfo($target_file_path, PATHINFO_DIRNAME);
$target_file_name = pathinfo($target_file_path, PATHINFO_BASENAME);

$xml_file_path = './reports/' . $target_file_name . '.xml';

for($i = 0; $i < $reps; $i++) {
    try {
        $coverage_obj = TEST_ROUTINE(random_string(1000));
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