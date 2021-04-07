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
        TEST_ROUTINE(random_string(1000));
        $xml_obj = get_xml_object($xml_file_path);
        $coverage = get_coverage_from_xml($xml_obj);
        $acc = get_acc_from_coverage($coverage);
        print_r($acc . ' ');
    } catch(Exception $e) {
        echo 'Exception Message : ' . $e->getMessage();
        return;
    }
}

function get_xml_object($file_path) {
    if(!($xml_obj = simplexml_load_file($file_path))) {
        throw new Exception("Cannot create xml object");
    }
    
    $xml_obj = $xml_obj->project;
    return $xml_obj;
}

function get_coverage_from_xml(SimpleXMLElement $xml_obj) {
    static $coverage = [];

    $file_num = 0;
    foreach($xml_obj->children() as $file) {
        if(!isset($file['name'])) 
            continue;
        
        foreach($file as $line) {
            if(!isset($line['type']) || (string)$line['type'] != 'stmt')
                continue;
            
            $count = (int)$line['count'];
            $line_num = (int)$line['num'];

            if(isset($coverage[$file_num][$line_num]))
                $coverage[$file_num][$line_num] += $count;
            else 
                $coverage[$file_num][$line_num] = $count;
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