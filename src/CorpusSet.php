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

        $prevCorpus = $this->getLastPickedCorpus();
        $this->totalBytes -= \strlen($prevCorpus->input);

        $this->queue[$this->lastPickedIdx] = new Corpus($input, $cov, $prevCorpus->energy);
        
        $this->totalBytes += \strlen($input);

        $this->maxInputLen = \max($this->maxInputLen, \strlen($input));
    }

    public function GetRandomOne() {
        if(count($this->queue) === 0) {
            return '';
        }
        $idx = rand(0, count($this->queue) - 1);
        return $this->queue[$idx];
    }

    public function pickOne() {
        $sumOfEnergy = $this->getSumOfEnergy();
        $randNum = rand(1, $sumOfEnergy);

        for($idx = 0; $idx < count($this->queue); $idx++) {
            $randNum -= $this->queue[$idx]->energy;
            if($randNum < 1) {
                return $this->pick($idx);
            }
        }

        return $this->pick(0);
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

    public function giveEnergyToLastPickedCorpus(int $energy) {
        $idx = $this->lastPickedIdx;
        if($idx === -1)
            return;

        $this->queue[$idx]->gainEnergy($energy);
    }

    public function depriveEnergyFromLastPickedCorpus(int $energy) {
        $idx = $this->lastPickedIdx;
        if($idx === -1)
            return;

        $this->queue[$idx]->loseEnergy($energy);
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

    public function getSumOfEnergy() {
        $sumOfEnergy = 0;
        for($i = 0; $i < count($this->queue); $i++) {
            $sumOfEnergy += $this->queue[$i]->energy;
        }
        return $sumOfEnergy;
    }

    // for test
    public function print() {
        $str = '';
        for($i = 0; $i < count($this->queue); $i++) {
            $str .= sprintf("%d(%db)", $this->queue[$i]->energy, strlen($this->queue[$i]->input)); 
            if($i !== count($this->queue) - 1)
                $str .= ", ";
        }
        echo $str . "\n";
    }
    private function pick($idx) {
        $this->lastPickedIdx = $idx;
        return $this->queue[$idx];
    }

}