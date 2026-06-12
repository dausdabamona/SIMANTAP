<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesiPenerimaanMakan extends Model
{
    protected $table = 'sesi_penerimaan_makans';

    protected $fillable = [
        'pemesanan_harian_id', 'tanggal', 'sesi',
        'porsi_dipesan', 'porsi_diterima', 'porsi_dimakan_taruna',
        'porsi_redistribusi', 'porsi_sisa', 'redistribusi_detail',
        'kondisi_makanan', 'catatan_kondisi',
        'waktu_serah_terima', 'lat', 'lng', 'foto',
        'status', 'diterima_by', 'diterima_at',
    ];

    protected $casts = [
        'tanggal'           => 'date',
        'redistribusi_detail' => 'array',
        'foto'              => 'array',
        'waktu_serah_terima'=> 'datetime',
        'diterima_at'       => 'datetime',
        'lat'               => 'decimal:7',
        'lng'               => 'decimal:7',
    ];

    const SESI_SARAPAN = 'sarapan';
    const SESI_SIANG   = 'siang';
    const SESI_MALAM   = 'malam';

    const STATUS_MENUNGGU    = 'menunggu';
    const STATUS_DITERIMA    = 'diterima';
    const STATUS_ADA_MASALAH = 'ada_masalah';

    const KONDISI_BAIK       = 'baik';
    const KONDISI_KURANG_BAIK = 'kurang_baik';
    const KONDISI_BURUK      = 'buruk';

    const KATEGORI_REDISTRIBUSI = [
        'petugas_ketarunaan' => 'Petugas Ketarunaan',
        'taruna_lain'        => 'Taruna Lain (tidak terdaftar)',
        'tamu'               => 'Tamu/Kunjungan',
        'lainnya'            => 'Lainnya',
    ];

    // ── Relationships ────────────────────────────────────────

    public function pemesananHarian(): BelongsTo
    {
        return $this->belongsTo(PemesananHarian::class, 'pemesanan_harian_id');
    }

    public function kehadiranMakan(): HasMany
    {
        return $this->hasMany(KehadiranMakan::class, 'sesi_id');
    }

    public function diterimaOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterima_by');
    }

    // ── Business logic ───────────────────────────────────────

    public function hitungPorsiTaruna(): int
    {
        return $this->kehadiranMakan()->where('hadir', true)->count();
    }

    public function rekonsiliasiValid(): bool
    {
        if ($this->porsi_diterima === null) {
            return false;
        }
        return (int) $this->porsi_diterima ===
            (int) ($this->porsi_dimakan_taruna ?? 0) +
            (int) $this->porsi_redistribusi +
            (int) $this->porsi_sisa;
    }

    // ── Accessors ────────────────────────────────────────────

    public function getSelisihAttribute(): int
    {
        return (int) ($this->porsi_diterima ?? 0) - (int) ($this->porsi_dimakan_taruna ?? 0);
    }

    public function getSesiLabelAttribute(): string
    {
        return match ($this->sesi) {
            self::SESI_SARAPAN => '🌅 Sarapan',
            self::SESI_SIANG   => '☀️ Siang',
            self::SESI_MALAM   => '🌙 Malam',
            default            => ucfirst($this->sesi),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU    => 'Menunggu Serah Terima',
            self::STATUS_DITERIMA    => 'Diterima',
            self::STATUS_ADA_MASALAH => 'Ada Masalah',
            default                  => ucfirst($this->status),
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU    => 'secondary',
            self::STATUS_DITERIMA    => 'success',
            self::STATUS_ADA_MASALAH => 'danger',
            default                  => 'secondary',
        };
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal', today());
    }

    public function scopePeriode($query, int $bulan, int $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
    }
}
