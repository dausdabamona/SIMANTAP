<?php

namespace App\Models;

use App\Enums\StatusKontrak;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KontrakMakan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kontrak_makan';

    protected $fillable = [
        'nomor_kontrak', 'tanggal_kontrak', 'tanggal_mulai', 'tanggal_selesai',
        'nilai_kontrak', 'harga_porsi', 'pihak_pertama', 'penyedia_id',
        'status', 'file_kontrak', 'file_addendum',
        'file_berita_acara_penunjukan', 'file_notulensi_rapat',
        'catatan', 'disetujui_oleh', 'disetujui_at',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'status'           => StatusKontrak::class,
        'tanggal_kontrak'  => 'date',
        'tanggal_mulai'    => 'date',
        'tanggal_selesai'  => 'date',
        'nilai_kontrak'    => 'decimal:2',
        'harga_porsi'      => 'decimal:2',
        'disetujui_at'     => 'datetime',
    ];

    public function penyedia(): BelongsTo
    {
        return $this->belongsTo(PenyediaMakan::class, 'penyedia_id');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function pemesananHarian(): HasMany
    {
        return $this->hasMany(PemesananHarian::class, 'kontrak_id');
    }

    public function jadwalMenu(): HasMany
    {
        return $this->hasMany(JadwalMenu::class, 'kontrak_id');
    }

    public function rekapBulanan(): HasMany
    {
        return $this->hasMany(RekapBulanan::class, 'kontrak_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function getIsAktifAttribute(): bool
    {
        $now = now()->toDateString();
        return $this->status === StatusKontrak::Aktif
            && $this->tanggal_mulai <= $now
            && $this->tanggal_selesai >= $now;
    }

    /** Nilai realisasi yang sudah dibayar */
    public function getTotalRealisasiAttribute(): float
    {
        return (float) $this->rekapBulanan()
            ->where('status', 'final')
            ->sum('nilai_bantuan');
    }

    public function getSisaNilaiKontrakAttribute(): float
    {
        return (float) $this->nilai_kontrak - $this->total_realisasi;
    }
}
