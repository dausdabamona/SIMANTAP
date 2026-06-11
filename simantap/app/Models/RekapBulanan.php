<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RekapBulanan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rekap_bulanan';

    protected $fillable = [
        'periode_bulan',
        'periode_tahun',
        'taruna_id',
        'total_porsi',
        'nilai_bantuan',
        'kontrak_id',
        'status',
    ];

    protected $casts = [
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'total_porsi'   => 'integer',
        'nilai_bantuan' => 'decimal:2',
    ];

    const STATUS_DRAFT                   = 'draft';
    const STATUS_DISETUJUI_WADIR         = 'disetujui_wadir';
    const STATUS_DIHITUNG_PPK            = 'dihitung_ppk';
    const STATUS_DITANDATANGANI_PEMBINA  = 'ditandatangani_pembina';
    const STATUS_DITANDATANGANI_PPK      = 'ditandatangani_ppk';
    const STATUS_DITANDATANGANI_KPA      = 'ditandatangani_kpa';
    const STATUS_FINAL                   = 'final';

    // ---------- Relationships ----------

    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class, 'taruna_id');
    }

    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(KontrakMakan::class, 'kontrak_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(RekapBulananApproval::class, 'rekap_bulanan_id');
    }

    // ---------- Scopes ----------

    public function scopeByPeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
    }

    public function scopeFinal($query)
    {
        return $query->where('status', self::STATUS_FINAL);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    // ---------- Accessors ----------

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

    public function getPeriodeLabelAttribute(): string
    {
        return $this->nama_bulan . ' ' . $this->periode_tahun;
    }
}
