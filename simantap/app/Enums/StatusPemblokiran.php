<?php

namespace App\Enums;

enum StatusPemblokiran: string
{
    case Diusulkan = 'diusulkan';
    case Diblokir  = 'diblokir';
    case Didebit   = 'didebit';

    public function label(): string
    {
        return match($this) {
            self::Diusulkan => 'Diusulkan',
            self::Diblokir  => 'Diblokir',
            self::Didebit   => 'Didebit ke Senat',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Diusulkan => 'badge bg-warning text-dark',
            self::Diblokir  => 'badge bg-info text-dark',
            self::Didebit   => 'badge bg-success',
        };
    }
}
