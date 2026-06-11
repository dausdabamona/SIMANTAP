<?php

namespace App\Enums;

enum StatusKontrak: string
{
    case Aktif    = 'aktif';
    case Berakhir = 'berakhir';

    public function label(): string
    {
        return match($this) {
            self::Aktif    => 'Aktif',
            self::Berakhir => 'Berakhir',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Aktif    => 'badge bg-success',
            self::Berakhir => 'badge bg-secondary',
        };
    }
}
