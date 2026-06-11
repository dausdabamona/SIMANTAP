<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringFoto extends Model
{
    use HasFactory;

    protected $table = 'monitoring_foto';

    protected $fillable = [
        'pemesanan_harian_id',
        'jenis_foto',
        'url_foto',
        'diunggah_oleh',
        'keterangan',
        'lat',
        'long',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'long' => 'decimal:7',
    ];

    public function pemesananHarian(): BelongsTo
    {
        return $this->belongsTo(PemesananHarian::class);
    }

    public function diunggahOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }

    public function scopeByJenisFoto($query, string $jenisFoto)
    {
        return $query->where('jenis_foto', $jenisFoto);
    }

    public function scopeSebelum($query)
    {
        return $query->where('jenis_foto', 'sebelum');
    }

    public function scopeSesudah($query)
    {
        return $query->where('jenis_foto', 'sesudah');
    }

    public function scopeDistribusi($query)
    {
        return $query->where('jenis_foto', 'distribusi');
    }
}
