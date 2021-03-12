<?php

require_once __DIR__ . '/Util.php';
require_once __DIR__ . '/CompressorInterface.php';

class RLECompressor implements CompressorInterface
{
    private $bucketMaxSize;

    public function __construct()
    {
        $this->bucketMaxSize = pow(2, 7) - 1;
    }

    private static function writeDuplicate($fd, array $buffer): void
    {
        fwrite($fd, Util::packInt8(count($buffer)));
        fwrite($fd, $buffer[0]);
    }

    private static function writeUnique($fd, array $buffer): void
    {
        fwrite($fd, Util::packInt8(-count($buffer)));
        foreach ($buffer as $char) {
            fwrite($fd, $char);
        }
    }

    public function compress(string $inputFilePath, string $outputFilePath): void
    {
        $inputResource  = fopen($inputFilePath, 'rb');
        $outputResource = fopen($outputFilePath, 'wb+');

        $charBuffer = [];
        while (!feof($inputResource)) {
            $bucketUnique   = true;
            $bucketRealSize = 1;

            for ($index = count($charBuffer); $index < $this->bucketMaxSize; $index++) {

                if (feof($inputResource)) {
                    break;
                }

                if (count($charBuffer) === 2) {
                    $bucketUnique = $charBuffer[0] !== $charBuffer[1];
                }

                $charBuffer[] = fread($inputResource, 1);
                $bucketRealSize++;

                if (count($charBuffer) === 1) {
                    continue;
                }

                if (!$bucketUnique && $charBuffer[count($charBuffer) - 2] !== $charBuffer[count($charBuffer) - 1]) {
                    $lastChar = array_pop($charBuffer);

                    self::writeDuplicate($outputResource, $charBuffer);

                    $charBuffer = [$lastChar];
                    break;
                }

                if ($bucketUnique && $charBuffer[count($charBuffer) - 2] === $charBuffer[count($charBuffer) - 1]) {
                    $prevChar     = array_pop($charBuffer);
                    $prevPrevChar = array_pop($charBuffer);

                    self::writeUnique($outputResource, $charBuffer);

                    $charBuffer = [$prevPrevChar, $prevChar];
                    break;
                }

                if (count($charBuffer) === $this->bucketMaxSize) {
                    if ($bucketUnique) {
                        self::writeUnique($outputResource, $charBuffer);
                    } else {
                        self::writeDuplicate($outputResource, $charBuffer);
                    }

                    $charBuffer = [];
                }
            }
        }

        if ($charBuffer[0] !== $charBuffer[1]) {
            self::writeUnique($outputResource, $charBuffer);
        } else {
            self::writeDuplicate($outputResource, $charBuffer);
        }

        fclose($inputResource);
        fclose($outputResource);
    }

    public function decompress(string $inputFilePath, string $outputFilePath): void
    {
        $inputResource  = fopen($inputFilePath, 'rb');
        $outputResource = fopen($outputFilePath, 'wb+');

        while (!feof($inputResource)) {
            $char = fread($inputResource, 1);

            if ($char === false) {
                break;
            }

            $size = Util::unpackInt8($char);

            if ($size === 0) {
                continue;
            }

            if ($size > 0) {
                $c = fread($inputResource, 1);
                for ($i = 0; $i < $size; $i++) {
                    fwrite($outputResource, $c);
                }
            } else {
                $line = fread($inputResource, abs($size));
                fwrite($outputResource, $line);
            }
        }

        fclose($inputResource);
        fclose($outputResource);
    }
}
