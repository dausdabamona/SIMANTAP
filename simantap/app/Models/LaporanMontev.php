<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanMontev extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'laporan_montev';

    protected $fillable = [
        'periode_bulan',
        'periode_tahun',
        'menu_dievaluasi',
        'nilai_gizi_rata',
        'catatan_prosedur',
        'hasil_evaluasi',
        'user_id',
    ];

    protected $casts = [
        'periode_bulan'  => 'integer',
        'periode_tahun'  => 'integer',
        'nilai_gizi_rata'=> 'decimal:2',
    ];

    // ---------- Relationships ----------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ---------- Scopes ----------

    public function scopeByPeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
    }

    public function scopeByTahun($query, int $tahun)
    {
        return $query->where('periode_tahun', $tahun);
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
}
