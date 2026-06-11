<?php

namespace App\Helpers;

class DateHelper
{
    private static array $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
        4 => 'April',   5 => 'Mei',       6 => 'Juni',
        7 => 'Juli',    8 => 'Agustus',   9 => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public static function namaBulan(int $num): string
    {
        return self::$bulan[$num] ?? '-';
    }

    public static function periodeLabel(int $bulan, int $tahun): string
    {
        return (self::$bulan[$bulan] ?? '-') . ' ' . $tahun;
    }
}
