<?php
namespace Fuzzer;

use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\CodeCoverage;


//branch coverage
class Coverage
{
    // SebastianBergmann\CodeCoverage\CodeCoverage
    public CodeCoverage $coverageObj;

    // Extracted coverage is probably accumulated coverage data.
    public array $curExtractedCoverage = [];   
    public array $prevExtractedCoverage;

    //branch coverage
    public array $curBranchCoverage = [];
    public array $prevBranchCoverage;

    //bucketed branch coverage
    public array $curCoverage = [];
    public array $prevCoverage;
    public array $accCoverage;
    public int $accCount = 0;
    public bool $wasInterested = false;


    public function __construct($sources) {
        $filter = new Filter;
        foreach($sources as $source) {
            $filter->includeDirectory($source);
            echo "<source> : " . $source . "\n";
        }

        //set coverageObj
        $this->coverageObj = new CodeCoverage(
            (new Selector)->forLineAndPathCoverage($filter),
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
        return $this->wasInterested;
    }

    public function getCurCoverage(): array {
        return $this->curCoverage;
    }

    public static function canFirstCoverSecond(array $firstCov, array $secondCov) {
        foreach ($secondCov as $file => $functions) {
            foreach ($functions as $functionName => $functionData) {
                foreach ($functionData as $branchId => $branch) {
                    foreach ($branch as $bucketId => $v) {
                        if(!isset($firstCov[$file][$functionName][$branchId][$bucketId])) {
                                return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    private function update(): void {
        $this->extractCoverageFromObj();
        $this->updateBranchCoverage();
        $this->updateBucketedCoverage();
    }

    // probably accumulated coverage data.
    private function extractCoverageFromObj(): void {
        $branch = [];
        $coverage = $this->coverageObj->getData();
        $functionCoverage = $coverage->functionCoverage();

        foreach ($functionCoverage as $file => $functions) {
            foreach ($functions as $functionName => $functionData) {
                foreach ($functionData['paths'] as $pathId => $pathData) {
                    if (count($pathData['hit']) >= 1) {
                        foreach ($pathData['path'] as $idx => $branchId) {
                            if(isset($branch[$file][$functionName][$branchId]))
                                $branch[$file][$functionName][$branchId] += count($pathData['hit']);
                            else 
                                $branch[$file][$functionName][$branchId] = count($pathData['hit']);
                        }
                    }
                }
            }
        }

        $this->prevExtractedCoverage = $this->curExtractedCoverage;
        $this->curExtractedCoverage = $branch;
    }


    // curCoverage = curExtractedCoverage - prevExtractedCoverage
    private function updateBranchCoverage(): void {
        $curBranchCoverage = [];
        foreach ($this->curExtractedCoverage as $file => $functions) {
            foreach ($functions as $functionName => $functionData) {
                foreach ($functionData as $branchId => $curAccCount) {
                    $prevAccCount = $this->prevExtractedCoverage[$file][$functionName][$branchId] ?? 0;
                    $curBranchCount = $curAccCount - $prevAccCount;
                    if($curBranchCount) $curBranchCoverage[$file][$functionName][$branchId] = $curBranchCount;
                }
            }
        }

        $this->prevBranchCoverage = $this->curBranchCoverage;
        $this->curBranchCoverage = $curBranchCoverage;
    }

    // should be called after updateBranchCoverage().
    private function updateBucketedCoverage(): void {
        $curCoverage = [];
        $wasInterested = false;
        foreach ($this->curBranchCoverage as $file => $functions) {
            foreach ($functions as $functionName => $functionData) {
                foreach ($functionData as $branchId => $branchCount) {
                    $bucketId = self::getBucketId($branchCount);
                    $curCoverage[$file][$functionName][$branchId][$bucketId] = true;

                    //update accCoverage
                    if(!isset($this->accCoverage[$file][$functionName][$branchId][$bucketId]))
                    {
                        $this->accCount += 1;
                        $wasInterested = true;
                        $this->accCoverage[$file][$functionName][$branchId][$bucketId] = true;
                    }
                    
                }
            }
        }

        $this->prevCoverage = $this->curCoverage;
        $this->curCoverage = $curCoverage;
        $this->wasInterested = $wasInterested;
    }


    private static function getBucketId($branchCount): int {
        if ($branchCount <= 3)
            return $branchCount - 1;
        else if ($branchCount <= 6)
            return 3;
        else if ($branchCount <= 8)
            return 4;
        else if ($branchCount <= 12)
            return 5;
        else if ($branchCount <= 20)
            return 6;
        else if ($branchCount <= 32)
            return 7;
        else if ($branchCount <= 64)
            return 8;
        else if ($branchCount <= 128)
            return 9;
        else
            return 10;
    }
}



