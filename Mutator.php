<?php
    class Mutator {
        
        private $input;

        function __construct(string $input) {
            $this->input = $input;
        }

        function setInput($str){
            $this->input = $str;
        }

        function getInput(){
            return $this->input;
        }

        function delete_random_character(){
            if($this->input === "")
                return $this->input;
            
            $len = strlen($this->input);
            $pos = rand(0, $len-1);
            $result;
            for($i=0; $i<$len; $i++){
                if($pos === $i){
                    $result[$i] = '';
                    continue;
                }
                $result[$i] = $this->input[$i];
            }

            $result = implode('', $result);
            return $result;
        }

        function insert_random_character(){
            $len = strlen($this->input);
            $pos = rand(0, $len);

            $random_char = chr(rand(32, 126));

            $result;
            $j=0;

            for($i=0; $i<$len+1; $i++){
                if($pos === $i)
                    $result[$i] = $random_char;
                else
                    $result[$i] = $this->input[$j++];
            }

            $result = implode('', $result);
            return $result;
        }

        function alternate_random_character(){
            if($this->input === "")
                return $this->input;
            
            $len = strlen($this->input);
            $pos = rand(0, $len-1);
            $random_char = $random_char = chr(rand(32, 126));
            $this->input[$pos] = $random_char;

            return $this->input;
        }

        function flip_random_character(){
            if($this->input === "")
                return $this->input;

            $pos = rand(0, strlen($this->input)-1);
            $c = $this->input[$pos];

            $bit = 1 << rand(0, 6);
            $new_c = chr(ord($c) ^ $bit);
            $result;
            
            for($i=0; $i<strlen($this->input); $i++){
                if($i===$pos)
                    $result[$i] = $new_c;
                else
                    $result[$i] = $this->input[$i];
            }

            $result = implode('', $result);
            return $result;
        }

        function mutate(){
            $r = rand(0, 3);
            switch ($r){
                case 0:
                    return $this->delete_random_character();
                case 1:
                    return $this->insert_random_character();
                case 2:
                    return $this->alternate_random_character();
                case 3:
                    return $this->flip_random_character();
            }
        }
    }

?>