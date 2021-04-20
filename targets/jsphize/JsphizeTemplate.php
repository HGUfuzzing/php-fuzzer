<?php
require_once __DIR__ . '/vendor/autoload.php';
use JsPhpize\JsPhpize;

function TEST_ROUTINE($input){
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
}

