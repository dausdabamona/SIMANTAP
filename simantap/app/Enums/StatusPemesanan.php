<?php

namespace App\Enums;

enum StatusPemesanan: string
{
    case Draft               = 'draft';
    case DiverifikasiPembina = 'diverifikasi_pembina';
    case DikirimPenyedia     = 'dikirim_penyedia';
    case Perubahan           = 'perubahan';
    case Disajikan           = 'disajikan';
    case Selesai             = 'selesai';

    public function label(): string
    {
        return match($this) {
            self::Draft               => 'Draft',
            self::DiverifikasiPembina => 'Diverifikasi Pembina',
            self::DikirimPenyedia     => 'Dikirim ke Penyedia',
            self::Perubahan           => 'Dalam Perubahan',
            self::Disajikan           => 'Disajikan',
            self::Selesai             => 'Selesai',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Draft               => 'badge bg-secondary',
            self::DiverifikasiPembina => 'badge bg-info text-dark',
            self::DikirimPenyedia     => 'badge bg-primary',
            self::Perubahan           => 'badge bg-warning text-dark',
            self::Disajikan           => 'badge bg-success',
            self::Selesai             => 'badge bg-dark',
        };
    }

    /** Transisi yang diizinkan dari status ini */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::Draft               => [self::DiverifikasiPembina],
            self::DiverifikasiPembina => [self::DikirimPenyedia, self::Draft],
            self::DikirimPenyedia     => [self::Perubahan, self::Disajikan],
            self::Perubahan           => [self::DikirimPenyedia],
            self::Disajikan           => [self::Selesai],
            self::Selesai             => [],
        };
    }
}
