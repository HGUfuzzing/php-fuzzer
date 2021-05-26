<?php
namespace Fuzzer;

use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Node\File;


//line coverage
class CoverageLine
{
    // SebastianBergmann\CodeCoverage\CodeCoverage
    public CodeCoverage $coverageObj;

    // Extracted coverage is probably accumulated coverage data.
    public array $curExtractedCoverage = [];   
    public array $prevExtractedCoverage;

    //line coverage
    public array $curLineCoverage = [];
    public array $prevLineCoverage;

    //bucketed line coverage
    public array $curCoverage = [];
    public array $prevCoverage;
    public array $accCoverage;
    public int $accCount = 0;
    public bool $wasTheLastInterested = false;


    public function __construct($sources) {
        $filter = new Filter;
        foreach($sources as $source) {
            $filter->includeDirectory($source);
            echo "<source> : " . $source . "\n";
        }

        //set coverageObj
        $this->coverageObj = new CodeCoverage(
            (new Selector)->forLineCoverage($filter),
            $filter
        );
    }

    public function start($path): void{
        $this->coverageObj->start($path, false);
    }

    public function stop(): void {
        $this->coverageObj->stop();
        $this->update();
    }

    public function getAccCount(): int {
        return $this->accCount;
    }

    public function wasInterested(): bool {
        return $this->wasTheLastInterested;
    }

    public function getCurCoverage(): array {
        return $this->curCoverage;
    }

    public static function canFirstCoverSecond(array $firstCov, array $secondCov) {
        foreach ($secondCov as $fileN => $file) {
            foreach ($file as $line => $cnt) {
                if(!isset($firstCov[$fileN][$line]))
                        return false;
            }
        }
        return true;
    }

    private function update(): void {
        $this->extractCoverageFromObj();
        $this->updateLineCoverage();
        $this->updateBucketedCoverage();
    }

    // probably accumulated coverage data.
    private function extractCoverageFromObj(): void {
        $coverage = [];
        $fileN = 0;

        $report = $this->coverageObj->getReport();

        // $fp = fopen('./object.txt', "w");
        // fwrite($fp, \serialize($report));
        // fclose($fp);
        // die();

        foreach ($report as $item) {
            // echo gettype($item) . "\n";
            if (! $item instanceof File) {
                continue;
            }
            // var_dump($item);
            // die();
            $coverageData = $item->lineCoverageData();

            foreach ($coverageData as $line => $data) {
                if ($data === null || !count($data)) {
                    continue;
                }
                
                if(!isset($coverage[$fileN][$line])) $coverage[$fileN][$line] = 0;
                $coverage[$fileN][$line] += count($data);
            }
            $fileN += 1;
        }

        $this->prevExtractedCoverage = $this->curExtractedCoverage;
        $this->curExtractedCoverage = $coverage;
    }

    // curCoverage = curExtractedCoverage - prevExtractedCoverage
    private function updateLineCoverage(): void {
        $curLineCoverage = [];

        foreach ($this->curExtractedCoverage as $fileN => $file) {
            foreach ($file as $line => $curAccCount) {
                $prevAccCount = $this->prevExtractedCoverage[$fileN][$line] ?? 0;
                $curLineCount = $curAccCount - $prevAccCount;
                if($curLineCount) $curLineCoverage[$fileN][$line] = $curLineCount;
            }
        }

        $this->prevLineCoverage = $this->curLineCoverage;
        $this->curLineCoverage = $curLineCoverage;
    }

    // should be called after updateLineCoverage().
    private function updateBucketedCoverage(): void {
        $curCoverage = [];
        $wasInterested = false;
        foreach ($this->curLineCoverage as $fileN => $file) {
            foreach ($file as $line => $lineCount) {
                $bucketId = self::getBucketId($lineCount);
                $curCoverage[$fileN][$line][$bucketId] = true;

                //update accCoverage
                if(!isset($this->accCoverage[$fileN][$line])) {
                    $this->accCount += 1;
                    $wasInterested = true;
                    $this->accCoverage[$fileN][$line] = true;
                }
            }
        }

        $this->prevCoverage = $this->curCoverage;
        $this->curCoverage = $curCoverage;
        $this->wasTheLastInterested = $wasInterested;
    }


    private static function getBucketId($count): int {
        if ($count <= 3)
            return $count - 1;
        else if ($count <= 6)
            return 3;
        else if ($count <= 8)
            return 4;
        else if ($count <= 12)
            return 5;
        else if ($count <= 20)
            return 6;
        else if ($count <= 32)
            return 7;
        else if ($count <= 64)
            return 8;
        else if ($count <= 128)
            return 9;
        else
            return 10;
    }
}



