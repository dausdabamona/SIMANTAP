<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanPembayaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengajuan_pembayaran';

    protected $fillable = [
        'nomor_pengajuan',
        'periode_bulan',
        'periode_tahun',
        'total_taruna',
        'total_porsi',
        'total_nilai',
        'status',
        'nomor_sp2d',
        'tanggal_sp2d',
        'invoice_penyedia',
        'bukti_transfer_kppn',
        'bukti_debit_bank',
        'bukti_transfer_penyedia',
    ];

    protected $casts = [
        'periode_bulan'  => 'integer',
        'periode_tahun'  => 'integer',
        'total_taruna'   => 'integer',
        'total_porsi'    => 'integer',
        'total_nilai'    => 'decimal:2',
        'tanggal_sp2d'   => 'date',
    ];

    const STATUS_DRAFT               = 'draft';
    const STATUS_DIPROSES_PPK        = 'diproses_ppk';
    const STATUS_DISETUJUI_KPA       = 'disetujui_kpa';
    const STATUS_PERMOHONAN_KPPN     = 'permohonan_kppn';
    const STATUS_SP2D                = 'sp2d';
    const STATUS_TRANSFER_KPPN       = 'transfer_kppn';
    const STATUS_DEBIT_BANK          = 'debit_bank';
    const STATUS_TRANSFER_PENYEDIA   = 'transfer_penyedia';
    const STATUS_KONFIRMASI_PENYEDIA = 'konfirmasi_penyedia';
    const STATUS_LPJ_PPK             = 'lpj_ppk';
    const STATUS_LPJ_KPA             = 'lpj_kpa';
    const STATUS_SELESAI             = 'selesai';

    // ---------- Relationships ----------

    public function workflow(): HasMany
    {
        return $this->hasMany(WorkflowPembayaran::class, 'pengajuan_id');
    }

    // ---------- Scopes ----------

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeByPeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
    }

    public function scopeAktif($query)
    {
        return $query->whereNotIn('status', [self::STATUS_SELESAI]);
    }

    // ---------- Accessors ----------

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT               => 'Draft',
            self::STATUS_DIPROSES_PPK        => 'Diproses PPK',
            self::STATUS_DISETUJUI_KPA       => 'Disetujui KPA',
            self::STATUS_PERMOHONAN_KPPN     => 'Permohonan KPPN',
            self::STATUS_SP2D                => 'SP2D Terbit',
            self::STATUS_TRANSFER_KPPN       => 'Transfer dari KPPN',
            self::STATUS_DEBIT_BANK          => 'Debit Bank',
            self::STATUS_TRANSFER_PENYEDIA   => 'Transfer ke Penyedia',
            self::STATUS_KONFIRMASI_PENYEDIA => 'Konfirmasi Penyedia',
            self::STATUS_LPJ_PPK             => 'LPJ PPK',
            self::STATUS_LPJ_KPA             => 'LPJ KPA',
            self::STATUS_SELESAI             => 'Selesai',
            default                          => ucfirst($this->status),
        };
    }

    public function getNamaBulanAttribute(): string
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April',   5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',   9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $bulan[$this->periode_bulan] ?? '-';
    }
}
