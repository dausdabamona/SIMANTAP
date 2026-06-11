<?php

namespace App\Enums;

enum StatusRekapBulanan: string
{
    case Draft                  = 'draft';
    case DihitungPpk            = 'dihitung_ppk';
    case DitandatanganiPembina  = 'ditandatangani_pembina';
    case DitandatanganiPpk      = 'ditandatangani_ppk';
    case DitandatanganiKpa      = 'ditandatangani_kpa';
    case Final                  = 'final';

    public function label(): string
    {
        return match($this) {
            self::Draft                 => 'Draft',
            self::DihitungPpk           => 'Dihitung PPK',
            self::DitandatanganiPembina => 'Ditandatangani Pembina Karakter',
            self::DitandatanganiPpk     => 'Ditandatangani PPK',
            self::DitandatanganiKpa     => 'Ditandatangani KPA',
            self::Final                 => 'Final',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Draft                 => 'badge bg-secondary',
            self::DihitungPpk           => 'badge bg-info text-dark',
            self::DitandatanganiPembina => 'badge bg-primary',
            self::DitandatanganiPpk     => 'badge bg-warning text-dark',
            self::DitandatanganiKpa     => 'badge bg-success',
            self::Final                 => 'badge bg-dark',
        };
    }
}
