<?php

require_once __DIR__ . '/Util.php';
require_once __DIR__ . '/CompressorInterface.php';

class LZ77Compressor implements CompressorInterface
{
    private $dictionaryLength;
    private $bufferLength;

    public function __construct(int $dictionaryLength)
    {
        $this->dictionaryLength = $dictionaryLength - 1;
        $this->bufferLength     = pow(2, 8) - 1;
    }

    private function findMatch(string $dictionary, string $buffer): array
    {
        if (strlen($buffer) === 0) {
            return [0, 0];
        }

        $found = [0, 0];
        for ($length = 1; $length <= strlen($buffer); $length++) {
            $bufferSubstr = substr($buffer, 0, $length);

            $strpos = strpos($dictionary, $bufferSubstr);
            if ($strpos !== false) {
                $found = [strlen($dictionary) - $strpos, strlen($bufferSubstr)];
            } else {
                break;
            }
        }

        return $found;
    }

    public function compress(string $inputFilePath, string $outputFilePath): void
    {
        $inputResource  = fopen($inputFilePath, 'rb');
        $outputResource = fopen($outputFilePath, 'wb+');

        $dictionary = '';
        $buffer     = fread($inputResource, $this->bufferLength);
        $pos        = 0;
        $freadPos   = ftell($inputResource);

        while ($pos < $freadPos) {

            [$offset, $length] = self::findMatch($dictionary, $buffer);

            if ($length === $this->bufferLength) {
                $char = fread($inputResource, 1);

                $dictionary .= substr($buffer, 0, 1);
                $dictionary = substr($dictionary, strlen($dictionary) - $this->dictionaryLength);

                $buffer = substr($buffer, 1);
                $buffer .= $char;
            } else {
                $char = substr($buffer, $length, 1);
            }

            fwrite($outputResource, Util::packUInt16($offset));
            fwrite($outputResource, Util::packUInt8($length));
            fwrite($outputResource, Util::packString($char));

            $pos += $length;
            $pos++;
            if ($length < $this->bufferLength) {
                $length++;
            }

            $dictionary .= substr($buffer, 0, $length);
            if (strlen($dictionary) > $this->dictionaryLength) {
                $dictionary = substr($dictionary, strlen($dictionary) - $this->dictionaryLength);
            }

            $buffer = substr($buffer, $length);
            $buffer .= fread($inputResource, $length);

            $freadPos = ftell($inputResource);
        }

        fclose($inputResource);
        fclose($outputResource);
    }

    public function decompress(string $inputFilePath, string $outputFilePath): void
    {
        $inputResource  = fopen($inputFilePath, 'rb');
        $outputResource = fopen($outputFilePath, 'wb+');
        $dictionary     = '';

        $limit = false;
        while (!feof($inputResource)) {
            $offset = Util::unpackUInt16(fread($inputResource, 2));
            $length = Util::unpackUInt8(fread($inputResource, 1));
            $char   = Util::unPackString(fread($inputResource, 1));

            if ($offset === 0 && $length === 0) {
                fwrite($outputResource, $char);
                $dictionary .= $char;
                continue;
            }

            $line       = substr($dictionary, strlen($dictionary) - $offset, $length) . $char;
            $dictionary .= $line;
            fwrite($outputResource, $line);

            if (strlen($dictionary) > $this->dictionaryLength) {
                $dictionary = substr($dictionary, strlen($dictionary) - $this->dictionaryLength);
                if (!$limit) {
                    $limit = true;
                }
            }
        }

        fclose($inputResource);
        fclose($outputResource);
    }
}
