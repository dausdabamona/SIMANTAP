<?php

namespace App\Enums;

enum StatusPembayaran: string
{
    case Draft               = 'draft';
    case DiprosesPpk         = 'diproses_ppk';
    case DisetujuiKpa        = 'disetujui_kpa';
    case PermohonanKppn      = 'permohonan_kppn';
    case Sp2d                = 'sp2d';
    case TransferKppn        = 'transfer_kppn';
    case DebitBank           = 'debit_bank';
    case TransferPenyedia    = 'transfer_penyedia';
    case KonfirmasiPenyedia  = 'konfirmasi_penyedia';
    case LpjPpk              = 'lpj_ppk';
    case LpjKpa              = 'lpj_kpa';
    case Selesai             = 'selesai';

    public function label(): string
    {
        return match($this) {
            self::Draft              => 'Draft',
            self::DiprosesPpk        => 'Diproses PPK',
            self::DisetujuiKpa       => 'Disetujui KPA',
            self::PermohonanKppn     => 'Permohonan ke KPPN',
            self::Sp2d               => 'SP2D Terbit',
            self::TransferKppn       => 'Transfer KPPN → Taruna',
            self::DebitBank          => 'Debit Bank → Senat',
            self::TransferPenyedia   => 'Transfer Senat → Penyedia',
            self::KonfirmasiPenyedia => 'Konfirmasi Penyedia',
            self::LpjPpk             => 'LPJ oleh PPK',
            self::LpjKpa             => 'LPJ oleh KPA',
            self::Selesai            => 'Selesai',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Draft, self::DiprosesPpk                  => 'badge bg-secondary',
            self::DisetujuiKpa, self::PermohonanKppn        => 'badge bg-info text-dark',
            self::Sp2d, self::TransferKppn                  => 'badge bg-primary',
            self::DebitBank, self::TransferPenyedia         => 'badge bg-warning text-dark',
            self::KonfirmasiPenyedia, self::LpjPpk          => 'badge bg-success',
            self::LpjKpa, self::Selesai                     => 'badge bg-dark',
        };
    }

    public function allowedTransitions(): array
    {
        return match($this) {
            self::Draft              => [self::DiprosesPpk],
            self::DiprosesPpk        => [self::DisetujuiKpa, self::Draft],
            self::DisetujuiKpa       => [self::PermohonanKppn, self::Draft],
            self::PermohonanKppn     => [self::Sp2d],
            self::Sp2d               => [self::TransferKppn],
            self::TransferKppn       => [self::DebitBank],
            self::DebitBank          => [self::TransferPenyedia],
            self::TransferPenyedia   => [self::KonfirmasiPenyedia],
            self::KonfirmasiPenyedia => [self::LpjPpk],
            self::LpjPpk             => [self::LpjKpa],
            self::LpjKpa             => [self::Selesai],
            self::Selesai            => [],
        };
    }

    /** Role yang boleh melakukan transisi */
    public function requiredRole(): string
    {
        return match($this) {
            self::Draft              => 'senat_taruna',
            self::DiprosesPpk        => 'ppk',
            self::DisetujuiKpa       => 'kpa',
            self::PermohonanKppn     => 'ppk',
            self::Sp2d               => 'ppk',
            self::TransferKppn       => 'ppk',
            self::DebitBank          => 'ppk',
            self::TransferPenyedia   => 'ppk',
            self::KonfirmasiPenyedia => 'ppk',
            self::LpjPpk             => 'ppk',
            self::LpjKpa             => 'kpa',
            self::Selesai            => 'kpa',
        };
    }
}
