<?php 
declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover as CloverReport;
use SebastianBergmann\CodeCoverage\Report\Text as TextReport;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;

//for target project
use League\Uri\Contracts\UriException;
use League\Uri\Contracts\UriInterface;
use League\Uri\Exceptions\SyntaxError;
use League\Uri\Exceptions\TemplateCanNotBeExpanded;
use League\Uri\UriTemplate\Template;
use League\Uri\UriTemplate\VariableBag;
use League\Uri\UriTemplate;



$_input = '';


function TEST_ROUTINE($S) {
    global $_input;
    $_input = $S;

    //setup
    $filter = new Filter;
    $filter->includeDirectory(__DIR__ . '/vendor/league/uri/src/UriTemplate/');
    $coverage_obj = new CodeCoverage(
        (new Selector)->forLineCoverage($filter),
        $filter
    );
    
    $coverage_obj->start(__FILE__);
        $syn_err_cnt = 0;
        $template = _split(200);
        $variables = [
            'path'     => _split(15),
            'segments' => [_split(15), _split(15), 15, true, _split(15), false, null],
            'query'    => _split(15),
            'more'     => [_split(15), _split(15)],
            _split(15) => [_split(15), _split(15)],
            _split(15) => [_split(15)],
        ];
        $newVariables = [_split(15) => _split(15)
        , _split(15) => _split(15)];
        $newAltVariables = [_split(15) => _split(15)
        , _split(15) => _split(15), _split(15) => _split(15)];
        try {
            $uriTemplate = new UriTemplate($template, $variables);
            $newTemplate = $uriTemplate->withDefaultVariables($newVariables);
            $altTemplate = $uriTemplate->withDefaultVariables($variables);
            $newAltTemplate = $uriTemplate->withDefaultVariables($newAltVariables);
        } catch(SyntaxError $e) {
            // echo ++$syn_err_cnt . ' ';
        }
    $coverage_obj->stop();

    return $coverage_obj;
}


function _split($len) {
    global $_input;
    $ret = substr($_input, 0, $len);
    $_input = substr($_input, $len);
    return $ret;
}