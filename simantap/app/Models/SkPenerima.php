<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkPenerima extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sk_penerima';

    protected $fillable = [
        'no_sk',
        'tanggal_sk',
        'periode_bulan',
        'periode_tahun',
        'file_sk',
        'total_penerima',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_sk' => 'date',
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'total_penerima' => 'integer',
    ];

    public function rekapBulanan(): HasMany
    {
        return $this->hasMany(RekapBulanan::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeByPeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
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
