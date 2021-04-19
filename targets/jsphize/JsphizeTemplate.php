<?php
declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';
use JsPhpize\JsPhpize;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Driver\Xdebug3Driver;
use SebastianBergmann\CodeCoverage\Report\Clover;
use SebastianBergmann\CodeCoverage\Report\Text;


function TEST_ROUTINE($input){
    $filter = new Filter();
    $filter->includeDirectory("../src/");
    $driver = new Xdebug3Driver($filter);

    $coverage_obj = new CodeCoverage(
        $driver,
        $filter
    );

    $coverage_obj->start(__FILE__);

    try {
        $jsPhpize = new JsPhpize();
        $result = $jsPhpize->renderCode($input);
    } 
    catch (Throwable $e) {
        echo "Throwable Exception!\n";
    }
    catch(DivisionByZeroError $e){
        echo "Division by Zero!\n";
    }
    catch(ArgumentCountError $e){
        echo "Argument Count Error!!\n";
    }
    catch(Exception $e){
        echo "Exception: $e->getMessage()\n\n";
    }

    $coverage_obj->stop();

    (new Clover)->process($coverage_obj, './reports/' . basename(__FILE__) . '.xml');
    return $coverage_obj;
}

?>
