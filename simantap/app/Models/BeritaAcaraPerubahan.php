<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeritaAcaraPerubahan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'berita_acara_perubahan';

    protected $fillable = [
        'pemesanan_id',
        'alasan',
        'solusi',
        'file',
        'status',
        'catatan_penolakan',
    ];

    // ---------- Relationships ----------

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(PemesananHarian::class, 'pemesanan_id');
    }

    // ---------- Scopes ----------

    public function scopeDiajukan($query)
    {
        return $query->where('status', 'diajukan');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }
}
