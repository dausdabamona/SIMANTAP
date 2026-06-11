<?php

namespace App\Models;

use App\Enums\StatusPemesanan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PemesananHarian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pemesanan_harian';

    protected $fillable = [
        'tanggal', 'kontrak_id', 'jumlah_taruna_hadir', 'jumlah_porsi',
        'harga_porsi_snapshot', 'nilai_makan_harian', 'status', 'catatan',
        'ttd_senat_id', 'ttd_senat_at', 'ttd_pembina_id', 'ttd_pembina_at',
        'catatan_pembina', 'dikirim_at', 'dikirim_oleh',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'status'                => StatusPemesanan::class,
        'tanggal'               => 'date',
        'harga_porsi_snapshot'  => 'decimal:2',
        'nilai_makan_harian'    => 'decimal:2',
        'ttd_senat_at'          => 'datetime',
        'ttd_pembina_at'        => 'datetime',
        'dikirim_at'            => 'datetime',
    ];

    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(KontrakMakan::class, 'kontrak_id');
    }

    public function ttdSenat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ttd_senat_id');
    }

    public function ttdPembina(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ttd_pembina_id');
    }

    public function dikirimOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }

    public function penerimaanMakan(): HasMany
    {
        return $this->hasMany(PenerimaanMakan::class, 'pemesanan_id');
    }

    public function monitoringFoto(): HasMany
    {
        return $this->hasMany(MonitoringFoto::class, 'pemesanan_id');
    }

    public function beritaAcaraPerubahan(): HasMany
    {
        return $this->hasMany(BeritaAcaraPerubahan::class, 'pemesanan_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeTanggal($query, string $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }

    /** Hitung ulang nilai makan harian */
    public function hitungNilai(): void
    {
        $this->jumlah_porsi       = $this->jumlah_taruna_hadir * config('simantap.makan.porsi_per_hari', 3);
        $this->nilai_makan_harian = $this->jumlah_porsi * $this->harga_porsi_snapshot;
    }

    public function getIsH1Attribute(): bool
    {
        return now()->toDateString() < $this->tanggal->toDateString();
    }
}
