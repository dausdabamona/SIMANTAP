<?php

namespace App\Models;

use App\Enums\JenisMakan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaanMakan extends Model
{
    use HasFactory;

    protected $table = 'penerimaan_makan';

    protected $fillable = [
        'pemesanan_id', 'taruna_id', 'tanggal', 'jenis_makan',
        'jumlah_porsi_diterima', 'status_eligibilitas', 'alasan_pengecualian',
        'file_lampiran_pengecualian', 'ditandatangani', 'ditandatangani_at',
        'latitude', 'longitude', 'alamat_lokasi', 'captured_at', 'created_by',
    ];

    protected $casts = [
        'jenis_makan'        => JenisMakan::class,
        'tanggal'            => 'date',
        'ditandatangani'     => 'boolean',
        'ditandatangani_at'  => 'datetime',
        'captured_at'        => 'datetime',
        'latitude'           => 'decimal:7',
        'longitude'          => 'decimal:7',
    ];

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(PemesananHarian::class, 'pemesanan_id');
    }

    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes

    public function scopeDapat($query)
    {
        return $query->where('status_eligibilitas', 'dapat');
    }

    public function scopeTidakDapat($query)
    {
        return $query->where('status_eligibilitas', 'tidak_dapat');
    }

    public function scopePeriode($query, int $bulan, int $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
    }

    // Business rule: tidak_dapat wajib ada lampiran
    public function getLampiranRequiredAttribute(): bool
    {
        return $this->status_eligibilitas === 'tidak_dapat';
    }

    public function getHasGeotagAttribute(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
