<?php

class Util
{
    public static function setFirstBit16(int $number): int
    {
        return $number | 0b1000000000000000;
    }

    public static function unsetFirstBit16(int $number): int
    {
        return $number & 0b0111111111111111;
    }

    public static function issetFirstBit16(int $number): bool
    {
        return ($number & 0b1000000000000000) > 0;
    }

    public static function setSecondBit16(int $number): int
    {
        return $number | 0b0100000000000000;
    }

    public static function unsetSecondBit16(int $number): int
    {
        return $number & 0b1011111111111111;
    }

    public static function issetSecondBit16(int $number): bool
    {
        return ($number & 0b0100000000000000) > 0;
    }

    public static function packUInt8(int $number): string
    {
        return pack('C', $number);
    }

    public static function unpackUInt8(string $line): int
    {
        $unpack = unpack('C', $line);

        return (int)$unpack[1];
    }

    public static function packInt8(int $number): string
    {
        return pack('c', $number);
    }

    public static function unpackInt8(string $line): int
    {
        $unpack = unpack('c', $line);

        return (int)$unpack[1];
    }

    public static function packUInt16(int $number): string
    {
        return pack('n', $number);
    }

    public static function unpackUInt16(string $line): int
    {
        $unpack = unpack('n', $line);

        return (int)$unpack[1];
    }

    public static function packString(string $line): string
    {
        return pack('a*', $line);
    }

    public static function unPackString(string $line): string
    {
        $unpack = unpack('a*', $line);

        return $unpack[1];
    }
}
