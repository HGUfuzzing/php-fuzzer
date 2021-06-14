This program is a greybox mutation-based fuzzer for PHP, which is for finding bugs by feeding random inputs generated based on code coverage of the PHP program measured by [phpunit/php-code-coverage](https://github.com/sebastianbergmann/php-code-coverage) .

And, This fuzzer is designed for study purposes. 

to get code coverage feedback and 

## Installation

`composer global require hgu/fuzzphp`

## Usage

To fuzz the target PHP program, the driver for it should be made like below example ([michelf/php-markdown](https://github.com/michelf/php-markdown))

**target-driver.php (example):**

```php
<?php //target-driver.php
require_once __DIR__ . '/vendor/autoload.php';

// Required: The driver is feeded with a single input string.
$fuzzer->TEST_ROUTINE = function ($input){
    $result = Michelf\Markdown::defaultTransform($input);
};

// Optional: Custom mutation operator for target program.
$fuzzer->addCustomMutationOperator(function(string $str, int $maxLen) {
    $result = "** test **\n" . $str;
    // echo "addEmphasis\n";
    return \strlen($result) <= $maxLen ? $result : null;
}, 'addEmphasis');

// Optional: Custom mutation operator for target program.
$fuzzer->addCustomMutationOperator(function(string $str, int $maxLen) {
    $result = "> test\n" . $str;
    // echo "addBlockQuotes\n";
    return \strlen($result) <= $maxLen ? $result : null;
}, 'addBlockQuotes');
```

**input.txt (example):**

```
# heading
## heading

[google](https://www.google.com/).

> aaaaaa
>> aaa
```

The fuzzer starts with initial input string, which is firstly feeded to target program and mutated to generate more other input strings to be feeded. 

And, while fuzzing, the code coverage is measured based on <source-dir> directory that source directory for target PHP program, which is, for example, `vendor/michelf/php-markdown/Michelf`.

```bash
fuzzphp target-driver.php input.txt <source-dir>
```

The fuzzer generates "interesting input" files in "output" directory when the fuzzer find them. 

"interesting input" means that a input which finds new code coverage at the time, which considered to have ability to finding another new coverage. 

The fuzzer holds "interesting inputs", and take one of them to mutate and feed to target on every repetition. 

At the time to feed target a input,  the fuzzer can detect hang or fatal error occured on the target and the input is stored in "output" directory.