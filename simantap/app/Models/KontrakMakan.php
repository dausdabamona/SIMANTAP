<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KontrakMakan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kontrak_makan';

    protected $fillable = [
        'nomor_kontrak',
        'tanggal_kontrak',
        'tanggal_mulai',
        'tanggal_selesai',
        'nilai_kontrak',
        'harga_porsi',
        'pihak_pertama',
        'penyedia_id',
        'status',
        'file_kontrak',
        'file_addendum',
        'berita_acara_penunjukan',
        'notulensi_rapat',
    ];

    protected $casts = [
        'tanggal_kontrak'  => 'date',
        'tanggal_mulai'    => 'date',
        'tanggal_selesai'  => 'date',
        'nilai_kontrak'    => 'decimal:2',
        'harga_porsi'      => 'decimal:2',
    ];

    // ---------- Relationships ----------

    public function penyedia(): BelongsTo
    {
        return $this->belongsTo(PenyediaMakan::class, 'penyedia_id');
    }

    public function pemesananHarian(): HasMany
    {
        return $this->hasMany(PemesananHarian::class, 'kontrak_id');
    }

    public function rekapBulanan(): HasMany
    {
        return $this->hasMany(RekapBulanan::class, 'kontrak_id');
    }

    // ---------- Scopes ----------

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeAktifPadaTanggal($query, string $tanggal)
    {
        return $query->where('status', 'aktif')
                     ->where('tanggal_mulai', '<=', $tanggal)
                     ->where('tanggal_selesai', '>=', $tanggal);
    }

    // ---------- Accessors ----------

    public function getDurasiHariAttribute(): int
    {
        return (int) $this->tanggal_mulai->diffInDays($this->tanggal_selesai);
    }
}
