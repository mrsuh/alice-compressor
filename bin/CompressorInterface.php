<?php

interface CompressorInterface
{
    public function compress(string $inputFilePath, string $outputFilePath): void;

    public function decompress(string $inputFilePath, string $outputFilePath): void;
}
