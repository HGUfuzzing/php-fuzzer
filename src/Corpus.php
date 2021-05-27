<?php

namespace Fuzzer;

use Fuzzer\Coverage;

class Corpus
{
    //fix to public
    public string $input;
    public array $coverage;
    public int $energy;

    public function __construct($input = '', $coverage = [], $energy = 10) {
        $this->input = $input;
        $this->coverage = $coverage;
        $this->energy = $energy;
    }
    
    public function gainEnergy(int $e) {
        $this->energy += $e;
    }

    public function loseEnergy(int $e) {
        $energy = $this->energy - $e;
        $this->energy = $energy > 1 ? $energy : 1;
    }
}