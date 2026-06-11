<?php

namespace App\Models;

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
        // Koreksi #2: PPK hanya menyetujui, bukan pihak kontrak
        'disetujui_ppk_id', 'tgl_persetujuan_ppk',
        'catatan', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'tanggal_kontrak'     => 'date',
        'tanggal_mulai'       => 'date',
        'tanggal_selesai'     => 'date',
        'tgl_persetujuan_ppk' => 'date',
        'nilai_kontrak'       => 'decimal:2',
        'harga_porsi'         => 'decimal:2',
    ];

    // Status constants
    const STATUS_DRAFT      = 'draft';
    const STATUS_AKTIF      = 'aktif';
    const STATUS_BERAKHIR   = 'berakhir';
    const STATUS_DIBATALKAN = 'dibatalkan';

    // ---------- Relationships ----------

    public function penyedia(): BelongsTo
    {
        return $this->belongsTo(PenyediaMakan::class, 'penyedia_id');
    }

    /** PPK yang menyetujui kontrak (bukan pihak penandatangan) */
    public function disetujuiPpk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_ppk_id');
    }

    public function jadwalMenu(): HasMany
    {
        return $this->hasMany(JadwalMenu::class, 'kontrak_id');
    }

    public function pemesananHarian(): HasMany
    {
        return $this->hasMany(PemesananHarian::class, 'kontrak_id');
    }

    public function rekapBulanan(): HasMany
    {
        return $this->hasMany(RekapBulanan::class, 'kontrak_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ---------- Scopes ----------

    public function scopeAktif($query)
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function scopeAktifPadaTanggal($query, string $tanggal)
    {
        return $query->where('status', self::STATUS_AKTIF)
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal);
    }

    // ---------- Accessors ----------

    public function getIsAktifAttribute(): bool
    {
        $today = now()->toDateString();
        return $this->status === self::STATUS_AKTIF
            && $this->tanggal_mulai->toDateString() <= $today
            && $this->tanggal_selesai->toDateString() >= $today;
    }

    public function getTotalRealisasiAttribute(): float
    {
        return (float) $this->rekapBulanan()->where('status', 'final')->sum('nilai_bantuan');
    }

    public function getSisaNilaiKontrakAttribute(): float
    {
        return (float) $this->nilai_kontrak - $this->total_realisasi;
    }
}
