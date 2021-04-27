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
            
            return  substr($this->input, 0, $pos) 
                    . substr($this->input, $pos+1);
        }

        function insert_random_character(){
            $len = strlen($this->input);
            $pos = rand(0, $len-1);

            $random_char = chr(rand(32, 126));

            return  substr($this->input, 0, $pos)
                    . $random_char
                    . substr($this->input, $pos);
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

            $bit = 1 << rand(0, 7);
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

        function insert_repeated_random_characters(){
            $len = strlen($this->input);
            $pos = rand(0, $len-1);
            $random_num = rand(0,10);
            $random_char = chr(rand(32, 126));

            return  substr($this->input, 0, $pos)
                    . str_repeat($random_char, $random_num)
                    . substr($this->input, $pos);
        }

        // increase/subtract 1~35  =>  arith 8
        function arithmetic_8bit(){
            $result = "";
            $str = $this->input;
            $len = strlen($str);
            
            $increase_val = 1;
            $decrease_val = -1;
            $is_increase_phase = true;

            for($i=0; $i<$len; $i++){
                if($is_increase_phase)
                {
                    $c = ord($str) + $increase_val;
                    $increase_val = ($increase_val + 1) % 36;
                }
                else 
                {
                    $c = ord($str) + $decrease_val;
                    $decrease_val = ($decrease_val - 1) % 36;
                }

                $str = substr($str, 1);
                $result = $result . chr($c);
            }

            return $result;
        }

        function mutate(){
            $r = rand(0, 5);
            switch ($r){
                case 0:
                    return $this->delete_random_character();
                case 1:
                    return $this->insert_random_character();
                case 2:
                    return $this->alternate_random_character();
                case 3:
                    return $this->flip_random_character();
                case 4:
                    return $this->insert_repeated_random_characters();
                case 5:
                    return $this->arithmetic_8bit();
            }
        }
    }

?>
