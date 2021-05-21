<?php
namespace Fuzzer;

use Fuzzer\Corpus;
use Fuzzer\Coverage;

class CorpusSet
{
    public array $queue = []; // array of Corpus
    public int $lastPickedIdx = -1; // -1 if have never picked.

    public int $totalBytes = 0;
    public int $maxInputLen = 0;

    public function __construct($initialInputs = [], $initialCoverages = []) {
        if(count($initialInputs) !== count($initialCoverages))
            throw Error('initialInputs and initialCoverages are should be same length.');
        
        $num = count($initialInputs);
    
        for ($i = 0; $i < $num; $i++) {
            $input = $initialInputs[$i];
            $cov = $initialCoverages[$i];
            $this->queue[] = new Corpus($input, $cov);
            
            $this->totalBytes += \strlen($input);
            $this->maxInputLen = \max($this->maxInputLen, \strlen($input));
        }
    }

    public function pushNewCorpus(string $input, array $cov) {
        $this->queue[] = new Corpus($input, $cov);
        $this->totalBytes += \strlen($input);
        $this->maxInputLen = \max($this->maxInputLen, \strlen($input));
    }

    public function replaceWithLastPickedCorpus(string $input, array $cov) {
        $qsize = count($this->queue);
        if($idx >= $qsize) {
            echo "[error] Tried replace invalid idx..(idx = {$idx}, qsize = {$qsize})\n";
            return;
        }

        $this->totalBytes -= \strlen($this->getLastPickedCorpus()->input);

        $this->queue[$this->lastPickedIdx] = new Corpus($input, $cov);
        
        $this->totalBytes += \strlen($input);

        $this->maxInputLen = \max($this->maxInputLen, \strlen($input));
    }


    public function pickOne() {
        if(count($this->queue) === 0) {
            $this->lastPickedIdx = -1;
            return '';
        }
        $idx = rand(0, count($this->queue) - 1);
        $this->lastPickedIdx = $idx;
        return $this->queue[$idx];
    }

    public function removeDuplicates() {
        $num = count($this->queue);
        for($i = 0; $i < $num; $i++) {
            for($j = 0; $j < $num; $j++) {
                if($i === $j) continue;

                if($this->queue[$i]->input == $this->queue[$j]->input) {
                    echo "Find Duplicated Input\n";
                    array_splice($this->queue, $j, 1);
                    $num--;
                    $j--;
                }
            }
        }
    }

    public function getLastPickedCorpus() {
        $idx = $this->lastPickedIdx;
        if($idx === -1)
            return null;
        return $this->queue[$idx];
    }

    public function getNumOfCorpus() {
        return count($this->queue);
    }

    public function getTotalBytes() {
        return $this->totalBytes;
    }
    
    public function getMaxInputLen() {
        return $this->maxInputLen;
    }

}