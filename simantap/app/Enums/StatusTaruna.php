<?php

namespace App\Enums;

enum StatusTaruna: string
{
    case Aktif                 = 'aktif';
    case Cuti                  = 'cuti';
    case Pesiar                = 'pesiar';
    case SakitDiKampus         = 'sakit_di_kampus';
    case SakitDiRumahKeluarga  = 'sakit_di_rumah_keluarga';
    case PenundaanStudi        = 'penundaan_studi';

    public function label(): string
    {
        return match($this) {
            self::Aktif                => 'Aktif',
            self::Cuti                 => 'Cuti',
            self::Pesiar               => 'Pesiar',
            self::SakitDiKampus        => 'Sakit (di Kampus)',
            self::SakitDiRumahKeluarga => 'Sakit (di Rumah Keluarga)',
            self::PenundaanStudi       => 'Penundaan Studi',
        };
    }

    public function mendapatBantuan(): bool
    {
        return match($this) {
            self::Aktif, self::SakitDiKampus => true,
            default                          => false,
        };
    }

    public function wajibLampiran(): bool
    {
        return match($this) {
            self::Cuti, self::Pesiar,
            self::SakitDiRumahKeluarga,
            self::PenundaanStudi => true,
            default              => false,
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Aktif                => 'badge bg-success',
            self::Cuti                 => 'badge bg-warning text-dark',
            self::Pesiar               => 'badge bg-info text-dark',
            self::SakitDiKampus        => 'badge bg-primary',
            self::SakitDiRumahKeluarga => 'badge bg-danger',
            self::PenundaanStudi       => 'badge bg-secondary',
        };
    }
}
