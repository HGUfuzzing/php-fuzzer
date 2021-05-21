<?php

namespace Fuzzer;

use Fuzzer\Coverage;

class Corpus
{
    //fix to public
    public string $input;
    public array $coverage;

    public function __construct($input = '', $coverage = []) {
        $this->input = $input;
        $this->coverage = $coverage;
    }
}