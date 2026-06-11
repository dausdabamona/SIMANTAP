<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeritaAcaraPerubahan extends Model
{
    use HasFactory;

    protected $table = 'berita_acara_perubahan';

    protected $fillable = [
        'pemesanan_harian_id',
        'no_berita_acara',
        'alasan',
        'jumlah_awal',
        'jumlah_akhir',
        'dibuat_oleh',
        'disetujui_oleh',
        'disetujui_at',
        'file_dokumen',
    ];

    protected $casts = [
        'jumlah_awal' => 'integer',
        'jumlah_akhir' => 'integer',
        'disetujui_at' => 'datetime',
    ];

    public function pemesananHarian(): BelongsTo
    {
        return $this->belongsTo(PemesananHarian::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function getSelisihAttribute(): int
    {
        return $this->jumlah_akhir - $this->jumlah_awal;
    }
}
