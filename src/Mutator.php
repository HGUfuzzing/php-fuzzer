<?php
namespace Fuzzer;

//Cross over 추가하기.
class Mutator {
    private array $mutators;
    private ?string $crossOverWith = null; 

    public function __construct() {
        $this->mutators = [
            [$this, 'mutateEraseBytes'],
            [$this, 'mutateInsertByte'],
            [$this, 'mutateInsertRepeatedBytes'],
            [$this, 'mutateChangeByte'],
            [$this, 'mutateChangeBit'],
            [$this, 'mutateShuffleBytes'],
            [$this, 'mutateChangeASCIIInt'],
            [$this, 'mutateChangeBinInt'],
            [$this, 'mutateCopyPart'],
            [$this, 'mutateCrossOver']
        ];
    }

    public function __call($method, $args)
    {
        if (isset($this->$method)) {
            $func = $this->$method;
            return call_user_func_array($func, $args);
        }
    }

    public function addCustomMutationOperator($func, $name) {
        $this->$name = $func;
        $this->mutators[] = ([$this, $name]);
    }

    public function getMutators(): array {
        return $this->mutators;
    }

    public function randomInt(int $maxExclusive): int {
        return \mt_rand(0, $maxExclusive - 1);
    }

    public function randomIntRange(int $minInclusive, $maxInclusive): int {
        return \mt_rand($minInclusive, $maxInclusive);
    }

    public function randomChar(): string {
        // TODO: Biasing?
        return \chr($this->randomInt(256));
    }

    public function randomPos(string $str): int {
        $len = \strlen($str);
        if ($len === 0) {
            throw new \Error("String must not be empty!");
        }
        return $this->randomInt($len);
    }

    public function randomPosOrEnd(string $str): int {
        return $this->randomInt(\strlen($str) + 1);
    }

    public function randomElement(array $array) {
        return $array[$this->randomInt(\count($array))];
    }

    public function randomBool(): bool {
        return (bool) \mt_rand(0, 1);
    }

    public function randomString(int $len): string {
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            $result .= $this->randomChar();
        }
        return $result;
    }

    private function randomBiasedChar(): string {
        if ($this->randomBool()) {
            return $this->randomChar();
        }
        $chars = "!*'();:@&=+$,/?%#[]012Az-`~.\xff\x00";
        return $chars[$this->randomPos($chars)];
    }


    

    public function mutateEraseBytes(string $str, int $maxLen): ?string {
        $len = \strlen($str);
        if ($len <= 1) {
            return null;
        }

        $minNumBytes = $maxLen < $len ? $len - $maxLen : 0;
        $maxNumBytes = min($minNumBytes + ($len >> 1), $len);
        $numBytes = $this->randomIntRange($minNumBytes, $maxNumBytes);
        $pos = $this->randomInt($len - $numBytes + 1);
        return \substr($str, 0, $pos)
            . \substr($str, $pos + $numBytes);
    }

    public function mutateInsertByte(string $str, int $maxLen): ?string {
        if (\strlen($str) >= $maxLen) {
            return null;
        }

        $pos = $this->randomPosOrEnd($str);
        return \substr($str, 0, $pos)
            . $this->randomBiasedChar()
            . \substr($str, $pos);
    }

    public function mutateInsertRepeatedBytes(string $str, int $maxLen): ?string {
        $minNumBytes = 3;
        $len = \strlen($str);
        if ($len + $minNumBytes >= $maxLen) {
            return null;
        }

        $maxNumBytes = min($maxLen - $len, 128);
        $numBytes = $this->randomIntRange($minNumBytes, $maxNumBytes);
        $pos = $this->randomPosOrEnd($str);
        // TODO: Biasing?
        $char = $this->randomChar();
        return \substr($str, 0, $pos)
            . str_repeat($char, $numBytes)
            . \substr($str, $pos);
    }

    public function mutateChangeByte(string $str, int $maxLen): ?string {
        if ($str === '' || \strlen($str) > $maxLen) {
            return null;
        }

        $pos = $this->randomPos($str);
        $str[$pos] = $this->randomBiasedChar();
        return $str;
    }

    public function mutateChangeBit(string $str, int $maxLen): ?string {
        if ($str === '' || \strlen($str) > $maxLen) {
            return null;
        }

        $pos = $this->randomPos($str);
        $bit = 1 << $this->randomInt(8);
        $str[$pos] = \chr(\ord($str[$pos]) ^ $bit);
        return $str;
    }

    public function mutateShuffleBytes(string $str, int $maxLen): ?string {
        $len = \strlen($str);
        if ($str === '' || $len > $maxLen) {
            return null;
        }
        $numBytes = $this->randomInt(min($len, 8)) + 1;
        $pos = $this->randomInt($len - $numBytes + 1);

        return \substr($str, 0, $pos)
            . \str_shuffle(\substr($str, $pos, $numBytes))
            . \substr($str, $pos + $numBytes);

    }

    public function mutateChangeASCIIInt(string $str, int $maxLen): ?string {
        $len = \strlen($str);
        if ($str === '' || $len > $maxLen) {
            return null;
        }

        $beginPos = $this->randomPos($str);
        while ($beginPos < $len && !\ctype_digit($str[$beginPos])) {
            $beginPos++;
        }
        if ($beginPos === $len) {
            return null;
        }
        $endPos = $beginPos;
        while ($endPos < $len && \ctype_digit($str[$endPos])) {
            $endPos++;
        }
        // TODO: We won't be able to get large unsigned integers here.
        $int = (int) \substr($str, $beginPos, $endPos - $beginPos);
        switch ($this->randomInt(4)) {
            case 0:
                $int++;
                break;
            case 1:
                $int--;
                break;
            case 2:
                $int >>= 1;
                break;
            case 3:
                $int <<= 1;
                break;
            default:
                assert(false);
        }

        $intStr = (string) $int;
        if ($len - ($endPos - $beginPos) + \strlen($intStr) > $maxLen) {
            return null;
        }

        return \substr($str, 0, $beginPos)
            . $intStr
            . \substr($str, $endPos);
    }

    public function mutateChangeBinInt(string $str, int $maxLen): ?string {
        $len = \strlen($str);
        if ($len > $maxLen) {
            return null;
        }

        $packCodes = [
            'C' => 1,
            'n' => 2, 'v' => 2,
            'N' => 4, 'V' => 4,
            'J' => 8, 'P' => 8,
        ];
        $packCode = $this->randomElement(array_keys($packCodes));
        $numBytes = $packCodes[$packCode];
        if ($numBytes > $len) {
            return null;
        }

        $pos = $this->randomInt($len - $numBytes + 1);
        if ($pos < 64 && $this->randomInt(4) == 0) {
            $int = $len;
        } else {
            $int = \unpack($packCode, $str, $pos)[1];
            $add = $this->randomIntRange(-10, 10);
            $int += $add;
            if ($add == 0 && $this->randomBool()) {
                $int = -$int;
            }
        }
        return \substr($str, 0, $pos)
             . \pack($packCode, $int)
             . \substr($str, $pos + $numBytes);
    }

    private function copyPartOf(string $from, string $to): string {
        $toLen = \strlen($to);
        $fromLen = \strlen($from);
        $toBeg = $this->randomPos($to);
        $numBytes = $this->randomInt($toLen - $toBeg) + 1;
        $numBytes = \min($numBytes, $fromLen);
        $fromBeg = $this->randomInt($fromLen - $numBytes + 1);
        return \substr($to, 0, $toBeg)
            . \substr($from, $fromBeg, $numBytes)
            . \substr($to, $toBeg + $numBytes);
    }

    private function insertPartOf(string $from, string $to, int $maxLen): ?string {
        $toLen = \strlen($to);
        if ($toLen >= $maxLen) {
            return null;
        }

        $fromLen = \strlen($from);
        $maxNumBytes = min($maxLen - $toLen, $fromLen);
        $numBytes = $this->randomInt($maxNumBytes) + 1;
        $fromBeg = $this->randomInt($fromLen - $numBytes + 1);
        $toInsertPos = $this->randomPosOrEnd($to);
        return \substr($to, 0, $toInsertPos)
            . \substr($from, $fromBeg, $numBytes)
            . \substr($to, $toInsertPos);
    }

    private function crossOver(string $str1, string $str2, int $maxLen): string {
        $maxLen = $this->randomInt($maxLen) + 1;
        $len1 = \strlen($str1);
        $len2 = \strlen($str2);
        $pos1 = 0;
        $pos2 = 0;
        $result = '';
        $usingStr1 = true;
        while (\strlen($result) < $maxLen && ($pos1 < $len1 || $pos2 < $len2)) {
            $maxLenLeft = $maxLen - \strlen($result);
            if ($usingStr1) {
                if ($pos1 < $len1) {
                    $maxExtraLen = min($len1 - $pos1, $maxLenLeft);
                    $extraLen = $this->randomInt($maxExtraLen) + 1;
                    $result .= \substr($str1, $pos1, $extraLen);
                    $pos1 += $extraLen;
                }
            } else {
                if ($pos2 < $len2) {
                    $maxExtraLen = min($len2 - $pos2, $maxLenLeft);
                    $extraLen = $this->randomInt($maxExtraLen) + 1;
                    $result .= \substr($str2, $pos2, $extraLen);
                    $pos2 += $extraLen;
                }
            }
            $usingStr1 = !$usingStr1;
        }
        return $result;
    }

    public function mutateCopyPart(string $str, int $maxLen): ?string {
        $len = \strlen($str);
        if ($str === '' || $len > $maxLen) {
            return null;
        }
        if ($len == $maxLen || $this->randomBool()) {
            return $this->copyPartOf($str, $str);
        } else {
            return $this->insertPartOf($str, $str, $maxLen);
        }
    }

    public function mutateCrossOver(string $str, int $maxLen): ?string {
        if ($this->crossOverWith === null) {
            return null;
        }
        $len = \strlen($str);
        if ($len > $maxLen || $len === 0 || \strlen($this->crossOverWith) === 0) {
            return null;
        }
        switch ($this->randomInt(3)) {
            case 0:
                return $this->crossOver($str, $this->crossOverWith, $maxLen);
            case 1:
                if ($len == $maxLen) {
                    return $this->insertPartOf($this->crossOverWith, $str, $maxLen);
                }
                /* fallthrough */
            case 2:
                return $this->copyPartOf($this->crossOverWith, $str);
            default:
                assert(false);
        }
    }

    public function mutate(string $str, ?string $crossOverWith): string {
        $this->crossOverWith = $crossOverWith;
        while (true) {
            $mutator = $this->randomElement($this->mutators);
            $newStr = $mutator($str, PHP_INT_MAX);
            if (null !== $newStr) {
                return $newStr;
            }
        }
    }
}
