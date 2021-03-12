<?php

require_once __DIR__ . '/Util.php';
require_once __DIR__ . '/CompressorInterface.php';

class NaiveCompressor implements CompressorInterface
{
    private $bufferLength;
    private $plainTextLength;
    private $dictionarySize;
    private $dictionaryWordMinLength    = 4;
    private $dictionaryWordMaxLength;
    private $dictionaryWordMinFrequency = 3;
    private $dictionaryWordSeparator    = '';

    public function __construct(int $bufferLength)
    {
        $this->bufferLength            = $bufferLength;
        $this->plainTextLength         = pow(2, 14);
        $this->dictionarySize          = pow(2, 14);
        $this->dictionaryWordMaxLength = pow(2, 8);
        $this->dictionaryWordSeparator = ' ';
    }

    private function getDictionary(string $text): array
    {
        $words = [];
        foreach (explode($this->dictionaryWordSeparator, str_replace(PHP_EOL, ' ', $text)) as $word) {
            $key = $word;

            if (strlen($key) < $this->dictionaryWordMinLength) {
                continue;
            }

            if (strlen($key) >= pow(2, 8)) {
                continue;
            }

            if (!array_key_exists($key, $words)) {
                $words[$key] = 0;
            }

            $words[$key]++;
        }

        $dictionary = [];
        foreach ($words as $word => $count) {
            if ($count < $this->dictionaryWordMinFrequency) {
                continue;
            }

            if (count($dictionary) >= $this->dictionarySize) {
                break;
            }

            $dictionary[] = $word;
        }

        return $dictionary;
    }

    private function getWordPositions(string $text, string $word): array
    {
        $lastPosition = 0;
        $positions    = [];
        while (($lastPosition = strpos($text, $word, $lastPosition)) !== false) {
            $positions[]  = $lastPosition;
            $lastPosition = $lastPosition + strlen($word);
        }

        return $positions;
    }

    private function writePlainText($resource, string $text): void
    {
        $lastPosition = 0;
        while (($buffer = substr($text, $lastPosition, $this->plainTextLength)) !== false) {
            $bufferLength = strlen($buffer);

            if ($bufferLength === 0) {
                break;
            }

            $bufferLengthWithBit = Util::unsetFirstBit16($bufferLength);
            $bufferLengthWithBit = Util::unsetSecondBit16($bufferLengthWithBit);
            fwrite($resource, Util::packUInt16($bufferLengthWithBit));
            fwrite($resource, Util::packString($buffer));

            $lastPosition += $bufferLength;
        }
    }

    private function writeDictionaryWord($resource, int $index): void
    {
        $indexWithBit = Util::unsetFirstBit16($index);
        $indexWithBit = Util::setSecondBit16($indexWithBit);
        fwrite($resource, Util::packUInt16($indexWithBit));
    }

    public function compress(string $inputFilePath, string $outputFilePath): void
    {
        $inputResource  = fopen($inputFilePath, 'rb');
        $outputResource = fopen($outputFilePath, 'wb+');

        while (!feof($inputResource)) {
            $buffer = fread($inputResource, $this->bufferLength);

            $dictionary = $this->getDictionary($buffer);

            $dictionarySize             = count($dictionary);
            $dictionarySizeWithFirstBit = Util::setFirstBit16($dictionarySize);

            fwrite($outputResource, Util::packUInt16($dictionarySizeWithFirstBit));
            foreach ($dictionary as $word) {
                fwrite($outputResource, Util::packUInt8(strlen($word)));
                fwrite($outputResource, Util::packString($word));
            }

            $substitutions = [];
            foreach ($dictionary as $word) {
                $positions = $this->getWordPositions($buffer, $word);

                foreach ($positions as $position) {
                    $substitutions[$position] = $word;
                }
            }

            ksort($substitutions);

            $lastPosition = 0;
            foreach ($substitutions as $position => $word) {

                if ($position < $lastPosition) {
                    continue;
                }

                $subBuffer = substr($buffer, $lastPosition, ($position - $lastPosition));

                $this->writePlainText($outputResource, $subBuffer);

                $wordIndex = array_search($word, $dictionary);

                $this->writeDictionaryWord($outputResource, $wordIndex);

                $lastPosition = $position + strlen($word);
            }

            $this->writePlainText($outputResource, substr($buffer, $lastPosition));
        }

        fclose($inputResource);
        fclose($outputResource);
    }

    public function decompress(string $inputFilePath, string $outputFilePath): void
    {
        $inputResource  = fopen($inputFilePath, 'rb');
        $outputResource = fopen($outputFilePath, 'wb+');

        $dictionary = [];
        while ($buff = fread($inputResource, 2)) {
            $number = Util::unpackUInt16($buff);

            if (Util::issetFirstBit16($number)) {
                $dictionary     = [];
                $dictionarySize = Util::unsetFirstBit16($number);

                for ($i = 0; $i < $dictionarySize; $i++) {
                    $wordLength   = Util::unpackUInt8(fread($inputResource, 1));
                    $dictionary[] = Util::unPackString(fread($inputResource, $wordLength));
                }

                continue;
            }

            if (Util::issetSecondBit16($number)) {
                $wordIndex = Util::unsetSecondBit16($number);
                $word      = $dictionary[$wordIndex];

                fwrite($outputResource, $word);
            } else {
                $textLength = Util::unsetSecondBit16($number);
                $text       = fread($inputResource, $textLength);

                fwrite($outputResource, $text);
            }
        }

        fclose($inputResource);
        fclose($outputResource);
    }
}

