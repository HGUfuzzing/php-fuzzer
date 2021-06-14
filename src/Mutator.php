<?php
/*
    This code is referenced from 
        https://github.com/nikic/PHP-Fuzzer/blob/master/src/Mutation/Mutator.php
*/

namespace Fuzzer;

class Mutator {
    private array $mutators;
    private array $input;

    public function __construct() {
        $this->mutate_functions = [
            [$this, 'delete_random_character'],
            [$this, 'insert_random_character'],
            [$this, 'alternate_random_character'],
            [$this, 'flip_1bit'],
            [$this, 'flip_2bits'],
            [$this, 'flip_4bits'],
            [$this, 'insert_repeated_random_characters']
        ];
    }

    public function __call($method, $args)
    {
        if (isset($this->$method)) {
            $func = $this->$method;
            return call_user_func_array($func, $args);
        }
    }

    public function addCustomMutationOperator($func, $name) {
        $this->$name = $func;
        $this->mutators[] = ([$this, $name]);
    }

    function delete_random_character($input){
        if($input === "")
            return $input;
        
        $len = strlen($input);
        $pos = rand(0, $len-1);
        
        return  substr($input, 0, $pos) 
                . substr($input, $pos+1);
    }

    function insert_random_character($input){
        $len = strlen($input);
        $pos = rand(0, $len-1);

        $random_char = chr(rand(32, 126));

        return  substr($input, 0, $pos)
                . $random_char
                . substr($input, $pos);
    }

    function alternate_random_character($input){
        if($input === "")
            return $input;
        
        $len = strlen($input);
        $pos = rand(0, $len-1);
        $random_char = $random_char = chr(rand(32, 126));
        $input[$pos] = $random_char;

        return $input;
    }

    function flip_1bit($input){
        if($input === "")
            return $input;

        $pos = rand(0, strlen($input)-1);
        $c = $input[$pos];

        $bit = 0b00000001 << rand(0, 7);
        $new_c = chr(ord($c) ^ $bit);
        
        return  substr($input, 0, $pos) 
                . $new_c 
                . substr($input, $pos+1);
    }

    function flip_2bits($input){
        if($input === "")
            return $input;

        $pos = rand(0, strlen($input)-1);
        $c = $input[$pos];

        $bit = 0b00000011 << rand(0, 6);
        $new_c = chr(ord($c) ^ $bit);

        return  substr($input, 0, $pos) 
                . $new_c 
                . substr($input, $pos+1);
    }

    function flip_4bits($input){
        if($input === "")
            return $input;

        $pos = rand(0, strlen($input)-1);
        $c = $input[$pos];

        $bit = 0b00001111 << rand(0, 4);
        $new_c = chr(ord($c) ^ $bit);
        
        return  substr($input, 0, $pos) 
                . $new_c 
                . substr($input, $pos+1);
    }

    function insert_repeated_random_characters($input){
        $len = strlen($input);
        $pos = rand(0, $len-1);
        $random_num = rand(0,10);
        $random_char = chr(rand(32, 126));

        return  substr($input, 0, $pos)
                . str_repeat($random_char, $random_num)
                . substr($input, $pos);
    }

    public function randomElement(array $array) {
        $num = \count($array);
        return $array[\mt_rand(0, $num - 1)];
    }
    
    public function mutate(string $input): string {
        while (true) {
            $mutator = $this->randomElement($this->mutate_functions);
            $newStr = $mutator($input);
            if (null !== $newStr) {
                return $newStr;
            }
        }
    }
}
