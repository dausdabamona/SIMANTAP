<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PemesananHarian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pemesanan_harian';

    protected $fillable = [
        'tanggal',
        'kontrak_id',
        'jumlah_taruna_hadir',
        'jumlah_porsi',
        'nilai_total',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal'              => 'date',
        'jumlah_taruna_hadir'  => 'integer',
        'jumlah_porsi'         => 'integer',
        'nilai_total'          => 'decimal:2',
    ];

    const STATUS_DRAFT                 = 'draft';
    const STATUS_DIVERIFIKASI_PEMBINA  = 'diverifikasi_pembina';
    const STATUS_DIKIRIM_PENYEDIA      = 'dikirim_penyedia';
    const STATUS_PERUBAHAN             = 'perubahan';
    const STATUS_DISAJIKAN             = 'disajikan';
    const STATUS_SELESAI               = 'selesai';

    // ---------- Relationships ----------

    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(KontrakMakan::class, 'kontrak_id');
    }

    public function beritaAcaraPerubahan(): HasMany
    {
        return $this->hasMany(BeritaAcaraPerubahan::class, 'pemesanan_id');
    }

    // ---------- Scopes ----------

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeMenungguVerifikasi($query)
    {
        return $query->where('status', self::STATUS_DIVERIFIKASI_PEMBINA);
    }

    public function scopeByTanggal($query, string $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }

    public function scopeByPeriode($query, string $dari, string $sampai)
    {
        return $query->whereBetween('tanggal', [$dari, $sampai]);
    }

    // ---------- Accessors ----------

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT                => 'Draft',
            self::STATUS_DIVERIFIKASI_PEMBINA => 'Diverifikasi Pembina',
            self::STATUS_DIKIRIM_PENYEDIA     => 'Dikirim ke Penyedia',
            self::STATUS_PERUBAHAN            => 'Dalam Perubahan',
            self::STATUS_DISAJIKAN            => 'Disajikan',
            self::STATUS_SELESAI              => 'Selesai',
            default                           => ucfirst($this->status),
        };
    }

    // ---------- Mutators ----------

    public function hitungJumlahPorsi(): void
    {
        $porsiPerHari      = config('simantap.porsi_per_hari', 3);
        $this->jumlah_porsi = $this->jumlah_taruna_hadir * $porsiPerHari;
    }

    public function hitungNilaiTotal(): void
    {
        $this->nilai_total = $this->jumlah_porsi * $this->kontrak->harga_porsi;
    }
}
