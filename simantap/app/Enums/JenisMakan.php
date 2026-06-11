<?php

namespace App\Enums;

enum JenisMakan: string
{
    case Sarapan    = 'sarapan';
    case MakanSiang = 'makan_siang';
    case MakanMalam = 'makan_malam';

    public function label(): string
    {
        return match($this) {
            self::Sarapan    => 'Sarapan',
            self::MakanSiang => 'Makan Siang',
            self::MakanMalam => 'Makan Malam',
        };
    }

    public function urutan(): int
    {
        return match($this) {
            self::Sarapan    => 1,
            self::MakanSiang => 2,
            self::MakanMalam => 3,
        };
    }
}
