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

        function flip_1bit(){
            if($this->input === "")
                return $this->input;

            $pos = rand(0, strlen($this->input)-1);
            $c = $this->input[$pos];

            $bit = 0b00000001 << rand(0, 7);
            $new_c = chr(ord($c) ^ $bit);
            
            return  substr($this->input, 0, $pos) 
                    . $new_c 
                    . substr($this->input, $pos+1);
        }

        function flip_2bits(){
            if($this->input === "")
                return $this->input;

            $pos = rand(0, strlen($this->input)-1);
            $c = $this->input[$pos];

            $bit = 0b00000011 << rand(0, 6);
            $new_c = chr(ord($c) ^ $bit);

            return  substr($this->input, 0, $pos) 
                    . $new_c 
                    . substr($this->input, $pos+1);
        }

        function flip_4bits(){
            if($this->input === "")
                return $this->input;

            $pos = rand(0, strlen($this->input)-1);
            $c = $this->input[$pos];

            $bit = 0b00001111 << rand(0, 4);
            $new_c = chr(ord($c) ^ $bit);
            
            return  substr($this->input, 0, $pos) 
                    . $new_c 
                    . substr($this->input, $pos+1);
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

        function mutate(){
            $r = rand(0, 7);
            switch ($r){
                case 0:
                    return $this->delete_random_character();
                case 1:
                    return $this->insert_random_character();
                case 2:
                    return $this->alternate_random_character();
                case 3:
                    return $this->insert_repeated_random_characters();
                case 4:
                    return $this->flip_1bit();
                case 5:
                    return $this->flip_2bits();
                case 6:
                    return $this->flip_4bits();
            }
        }
    }

?>
