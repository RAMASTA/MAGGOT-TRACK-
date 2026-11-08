<?php

namespace App\Support;

class Fmt
{
    /** Format angka gaya Indonesia: 1.234,5 (nol di belakang koma dibuang). */
    public static function num($v, int $dec = 2): string
    {
        $s = number_format((float) $v, $dec, ',', '.');
        if ($dec > 0 && str_contains($s, ',')) {
            $s = rtrim(rtrim($s, '0'), ',');
        }
        return $s;
    }
}
