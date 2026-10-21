<?php

namespace App\Support;

class NumberToWords
{
    private static array $ones = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
        'sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas',
        'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas',
    ];

    private static array $tens = [
        '', '', 'dua puluh', 'tiga puluh', 'empat puluh', 'lima puluh',
        'enam puluh', 'tujuh puluh', 'delapan puluh', 'sembilan puluh',
    ];

    public static function toWords(int $number): string
    {
        if ($number === 0) {
            return 'nol';
        }

        if ($number < 0) {
            return 'minus '.self::toWords(abs($number));
        }

        $parts = [];
        $billions = (int) ($number / 1000000000);
        $millions = (int) (($number % 1000000000) / 1000000);
        $thousands = (int) (($number % 1000000) / 1000);
        $remainder = $number % 1000;

        if ($billions > 0) {
            $parts[] = self::threeDigits($billions).' miliar';
        }
        if ($millions > 0) {
            $parts[] = self::threeDigits($millions).' juta';
        }
        if ($thousands > 0) {
            $parts[] = self::threeDigits($thousands).' ribu';
        }
        if ($remainder > 0) {
            $parts[] = self::threeDigits($remainder);
        }

        return implode(' ', $parts);
    }

    private static function threeDigits(int $n): string
    {
        $result = '';

        $hundreds = (int) ($n / 100);
        $remainder = $n % 100;

        if ($hundreds > 0) {
            if ($hundreds === 1) {
                $result = 'seratus';
            } else {
                $result = self::$ones[$hundreds].' ratus';
            }
        }

        if ($remainder > 0) {
            if ($remainder < 20) {
                $result .= ($result ? ' ' : '').self::$ones[$remainder];
            } else {
                $tens = (int) ($remainder / 10);
                $ones = $remainder % 10;
                $result .= ($result ? ' ' : '').self::$tens[$tens];
                if ($ones > 0) {
                    $result .= ' '.self::$ones[$ones];
                }
            }
        }

        return $result;
    }
}
