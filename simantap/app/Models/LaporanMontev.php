<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanMontev extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'laporan_montev';

    protected $fillable = [
        'periode_bulan',
        'periode_tahun',
        'dibuat_oleh',
        'isi_laporan',
        'rekomendasi',
        'file_laporan',
        'temuan',
        'status',
    ];

    protected $casts = [
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'temuan' => 'array',
    ];

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function scopeByPeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
    }

    public function scopeFinal($query)
    {
        return $query->where('status', 'final');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function getNamaPeriodeAttribute(): string
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return ($bulan[$this->periode_bulan] ?? $this->periode_bulan) . ' ' . $this->periode_tahun;
    }
}
