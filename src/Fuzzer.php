<?php
namespace Fuzzer;

use Fuzzer\Mutator;
use Fuzzer\Coverage;
use Fuzzer\CorpusSet;

class Fuzzer
{
    public $argv;

    public $targetFilePath;
    public $inputFilePath;
    public $targetSources;
    public $outputDir;
    
    public ?Coverage $coverage = null;

    public ?CorpusSet $corpusSet = null;  //corpus object로! (save current input 등의 functon도 corpus class 로 이동.)
    public $curInput;
    public $mutator;

    public $timeout = 3;
    public $maxRuns = PHP_INT_MAX;
    public $runs = 0;
    public $startTime = 0;

    public function __construct($argv) {
        $this->outputDir = getcwd() . '/output';
        $this->argv = $argv;
    }

    public function __call($method, $args)
    {
        if (isset($this->$method)) {
            $func = $this->$method;
            return call_user_func_array($func, $args);
        }
    }

    public function init() {
        $this->handleCmdLineArgs();
        $this->coverage = new Coverage($this->targetSources);

        $this->runs = 0;
        $this->startTime = microtime(true);

        $initial_input = $this->readTextFromFile($this->inputFilePath);
        $this->TEST($initial_input);
        $this->corpusSet = new CorpusSet([$initial_input], [$this->coverage->getCurCoverage()]);

        $this->setTimeoutHandler();
        $this->setShutdownHandler();

        $targetFilePath = $this->targetFilePath;
        (static function(Fuzzer $fuzzer) use($targetFilePath) {
            require $targetFilePath;
        })($this);

    }

    public function run() {
        // if didn't call init() yet.
        if(!$this->coverage || !$this->corpusSet) {
            return;
        }
        
        while($this->runs < $this->maxRuns) {
            try {
                if($this->runs % 500 === 0) {
                    gc_collect_cycles();
                    $this->corpusSet->removeDuplicates();
                    $time = microtime(true) - $this->startTime;
                    echo "Clear (runs: {$this->runs}, time: {$time}s)\n";
                }

                if (extension_loaded('pcntl'))
                    pcntl_alarm($this->timeout);
                
                $origInput = $this->corpusSet->pickOne()->input;
                $newInput = self::mutate($origInput);

                $this->TEST($newInput);

                $newCov = $this->coverage->getCurCoverage();
                $origCov = $this->corpusSet->getLastPickedCorpus()->coverage;
                
                //INTEREST
                if($this->coverage->wasInterested()) 
                {
                    //add to queue
                    $this->corpusSet->pushNewCorpus($newInput, $this->coverage->getCurCoverage());

                    //save the input as file
                    $this->saveCurrentInput('INTEREST');

                    //print action
                    $this->printAction('INTEREST');

                }
                //REDUCE
                else if(\strlen($newInput) < \strlen($origInput) &&
                    Coverage::canFirstCoverSecond($newCov, $origCov)) {
                    // remove orig input file and save cur input file
                    $this->removeInputFile('INTEREST', $origInput);
                    $this->saveCurrentInput('INTEREST');

                    //replace orig input with new input
                    $this->corpusSet->replaceWithLastPickedCorpus($newInput, $newCov);

                    //print action
                    $this->printAction('REDUCE');
                }
                else {
                    // echo "@\n";
                    // what if it wouldn't find something for a long time?
                }
            }
            catch(Exception $e) {
                // echo 'Exception Message : ' . $e->getMessage();
            } catch(Error $e) {
                echo "\n" . 'Error Message : ' . $e->getMessage() . "\n";
            }
        }
    }


    private function handleCmdLineArgs() {
        if(!isset($this->argv[1]) || !isset($this->argv[2]) || !isset($this->argv[3])) 
            die('usage : php ' . pathinfo(__FILE__, PATHINFO_BASENAME) . " <target_file> <input_file> <source1>[, <source2>, <source3>, ... ]\n");

        $this->targetFilePath = $this->argv[1];
        $this->inputFilePath = $this->argv[2];
        $this->targetSources = [];

        for($i = 3; $i < count($this->argv); $i++) {
            if(! file_exists($this->argv[$i])) die('"' .  $this->argv[$i] . '" is not exist.');
            $this->targetSources [] = $this->argv[$i];
        }
        if(! file_exists($this->targetFilePath)) die('The target file is not exist.');
        if(! file_exists($this->inputFilePath)) die('The input file is not exist.');

        echo "<target file> : $this->targetFilePath \n";
        echo "<input file> : $this->inputFilePath\n";
    }

    private function TEST(string $input) {
        $this->runs++;
        $this->coverage->start($this->targetFilePath);
        $this->curInput = $input;
        $this->TEST_ROUTINE($input);   // defined from target driver.
        $this->coverage->stop();
    }


    private function pickOneFromQ() {
        $idx = rand(0, count($this->queue) - 1);
        return $this->queue[$idx];
    }

    private static function mutate($input) {
        $mutObj = new Mutator($input);

        $times = rand(1, 3);
        $mutObj->mutate($times);

        return $mutObj->getInput();
    }

    private function readTextFromFile($file) {
        $fp = fopen($file, "r");
    
        $text = '';
        while( !feof($fp) ) {
            $text .= fgets($fp);
        }
    
        fclose($fp);
    
        return $text;
    }

    private function setShutdownHandler() {
        \register_shutdown_function(function() {
            $error = \error_get_last();
            if ($error === null) {
                return;
            }
            
            $crashInfo = "Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}";
    
            $this->saveCurrentInput('CRASH');
        });
    }

    private function setTimeoutHandler() {
        if (extension_loaded('pcntl')) {
            pcntl_signal(SIGALRM, function() {
                $this->saveCurrentInput('HANG');
                throw new Error("Timeout exceeded\n");
            });
            pcntl_async_signals(true);
        }
    }

    private function saveCurrentInput($type) {
        $hash = \md5($this->curInput);
        $path = $this->getFilePath($type, $hash);
    
        if (!file_exists($this->outputDir)) {
            mkdir($this->outputDir, 0777, true);
            echo "\nmkdir {$this->outputDir}\n";
        }

        $result = \file_put_contents($path, $this->curInput);
        assert($result === \strlen($this->curInput));

        // echo "@@ [saved] ({$path})\n";
    }


    private function removeInputFile($type, $input) {
        $hash = \md5($input);
        $path = $this->getFilePath($type, $hash);
        if (file_exists($path)) {
            // echo "@@ [deleted] ({$path})\n";
            unlink($path);
        }
        else {
            echo "????????????????????? {$path}\n";
        }
    }

    private function getFilePath($type, $hash) {
        return $this->outputDir . '/' . $type . '-' . $hash . '.txt';
    }

    private function printAction(string $action) {
        $time = microtime(true) - $this->startTime;
        $mem = memory_get_usage();
        $accCovsCnt = $this->coverage->getAccCount();
        $numCorpus = $this->corpusSet->getNumOfCorpus();

        $maxLen = $this->corpusSet->getMaxInputLen();
        $maxLenSize = \strlen((string)$maxlen);
        echo sprintf(
            "%-10s run: %d (%4.0f runs/s]), accCovs: %d (%.0f covs/s]), corp: %d (%s), len: %{$maxLenSize}/%d, time: %.0fs, mem: %s\n",
            $action, $this->runs, $this->runs / $time,
            $accCovsCnt, $accCovsCnt / $time,
            $numCorpus,
            self::formatBytes($this->corpusSet->getTotalBytes()),
            \strlen($this->corpusSet->getLastPickedCorpus()->input), $maxLen,
            $time, self::formatBytes($mem));
    }


    private static function formatBytes(int $bytes): string {
        if ($bytes < 10 * 1024) {
            return $bytes . 'b';
        } else if ($bytes < 10 * 1024 * 1024) {
            $kiloBytes = (int) round($bytes / 1024);
            return $kiloBytes . 'kb';
        } else {
            $megaBytes = (int) round($bytes / (1024 * 1024));
            return $megaBytes . 'mb';
        }
    }
}