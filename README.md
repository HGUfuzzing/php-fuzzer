This program is a `greybox mutation-based fuzzer for PHP`, which is for finding bugs by feeding random inputs generated based on code coverage of the PHP program measured by [phpunit/php-code-coverage](https://github.com/sebastianbergmann/php-code-coverage).


## Installation

`composer global require hgu/fuzzphp` **(not yet)**

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

**input-dir/1.txt (example):**

```
# heading
## heading

[google](https://www.google.com/).

> aaaaaa
>> aaa
```

**input-dir/2.txt (example):**
```
![alternative text](http://www.gstatic.com/webp/gallery/5.jpg "description")

![Kayak][logo]

[logo]: http://www.gstatic.com/webp/gallery/2.jpg "To go kayaking."
```

The fuzzer starts with initial inputs stored in `<input-dir>` , which are firstly feeded to target program and mutated to generate more other input strings to be feeded. 

And, while fuzzing, the code coverage is measured based on `<source-dir>` directory that source directory for target PHP program, which is, for example, `vendor/michelf/php-markdown/Michelf`.

```bash
fuzzphp target-driver.php <input-dir> <source-dir>
```

The fuzzer generates "interesting input" files in "output" directory when the fuzzer find them. 

"interesting input" means that a input which finds new code coverage at the time, which considered to have ability to finding another new coverage. 

The fuzzer holds "interesting inputs", and take one of them to mutate and feed to target on every repetition. 

At the time to feed target a input,  the fuzzer can detect hang or fatal error occured on the target and the input is stored in "output" directory.
