<?php

require_once __DIR__ . '/LZ77Compressor.php';
require_once __DIR__ . '/LZ77MCompressor.php';
require_once __DIR__ . '/RLECompressor.php';
require_once __DIR__ . '/NaiveCompressor.php';

$type               = $argv[1];
$action             = $argv[2];
$dictionaryLengthKB = $argv[3];
$inputFilePath      = $argv[4];
$outputFilePath     = $argv[5];

$dictionaryLength = 0;
switch ($dictionaryLengthKB) {
    case '2KB':
        $dictionaryLength = pow(2, 11);
        break;
    case '4KB':
        $dictionaryLength = pow(2, 12);
        break;
    case '8KB':
        $dictionaryLength = pow(2, 13);
        break;
    case '16KB':
        $dictionaryLength = pow(2, 14);
        break;
    case '32KB':
        $dictionaryLength = pow(2, 15);
        break;
    case '64KB':
        $dictionaryLength = pow(2, 16);
        break;
    case '128KB':
        $dictionaryLength = pow(2, 17);
        break;
    case '256KB':
        $dictionaryLength = pow(2, 18);
        break;
    default:
        throw new \Exception('Invalid buffer size');
}

switch ($type) {
    case 'naive':
        $compressor = new NaiveCompressor($dictionaryLength);
        break;
    case 'rle':
        $compressor = new RLECompressor();
        break;
    case 'lz77':
        $compressor = new LZ77Compressor($dictionaryLength);
        break;
    case 'lz77m':
        $compressor = new LZ77MCompressor($dictionaryLength);
        break;
    default:
        throw new \Exception('Invalid type');
}

switch ($action) {
    case 'compress':
        $compressor->compress($inputFilePath, $outputFilePath);
        break;
    case 'decompress':
        $compressor->decompress($inputFilePath, $outputFilePath);
        break;
    default:
        throw new \Exception('Invalid action');
}
