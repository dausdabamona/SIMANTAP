<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanBama extends Model
{
    protected $table = 'laporan_bamas';

    protected $fillable = [
        'periode_bulan', 'periode_tahun', 'status',
        'ringkasan_eksekutif', 'rekomendasi', 'permasalahan',
        'file_pdf', 'file_docx', 'tautan_gdrive',
        'dibuat_by',
        'disetujui_wadir_by', 'disetujui_wadir_at',
        'disetujui_kpa_by',   'disetujui_kpa_at',
        'dikirim_pusdik_at',
    ];

    protected $casts = [
        'rekomendasi'         => 'array',
        'permasalahan'        => 'array',
        'disetujui_wadir_at'  => 'datetime',
        'disetujui_kpa_at'    => 'datetime',
        'dikirim_pusdik_at'   => 'datetime',
    ];

    const STATUS_DRAFT           = 'draft';
    const STATUS_DISETUJUI_WADIR = 'disetujui_wadir';
    const STATUS_DISETUJUI_KPA   = 'disetujui_kpa';
    const STATUS_DIKIRIM_PUSDIK  = 'dikirim_pusdik';

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_by');
    }

    public function disetujuiWadirOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_wadir_by');
    }

    public function disetujuiKpaOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_kpa_by');
    }

    public function getPeriodeLabelAttribute(): string
    {
        $bulan = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
            5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
            9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
        ];
        return ($bulan[$this->periode_bulan] ?? $this->periode_bulan) . ' ' . $this->periode_tahun;
    }

    public function getTerlambatAttribute(): bool
    {
        // Batas kirim: tanggal 10 bulan berikutnya
        $batas = \Carbon\Carbon::create($this->periode_tahun, $this->periode_bulan, 10)->addMonth();
        return now()->isAfter($batas);
    }
}
