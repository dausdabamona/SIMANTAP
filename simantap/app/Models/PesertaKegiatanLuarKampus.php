<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaKegiatanLuarKampus extends Model
{
    protected $table = 'peserta_kegiatan_luar_kampus';

    protected $fillable = [
        'kegiatan_id', 'taruna_id', 'hari_hadir', 'nilai_bantuan', 'file_daftar_hadir',
    ];

    protected $casts = [
        'hari_hadir'    => 'integer',
        'nilai_bantuan' => 'decimal:2',
    ];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(KegiatanLuarKampus::class, 'kegiatan_id');
    }

    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class, 'taruna_id');
    }
}
