<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaanMakan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penerimaan_makan';

    protected $fillable = [
        'tanggal',
        'taruna_id',
        'jenis_makan',
        'jumlah_porsi_diterima',
        'status_eligibilitas',
        'alasan_pengecualian',
        'file_lampiran_pengecualian',
        'lat',
        'long',
    ];

    protected $casts = [
        'tanggal'               => 'date',
        'jumlah_porsi_diterima' => 'integer',
        'lat'                   => 'decimal:8',
        'long'                  => 'decimal:8',
    ];

    // ---------- Relationships ----------

    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class, 'taruna_id');
    }

    public function foto(): HasMany
    {
        return $this->hasMany(MonitoringFoto::class, 'penerimaan_id');
    }

    // ---------- Scopes ----------

    public function scopeDapat($query)
    {
        return $query->where('status_eligibilitas', 'dapat');
    }

    public function scopeTidakDapat($query)
    {
        return $query->where('status_eligibilitas', 'tidak_dapat');
    }

    public function scopeByTaruna($query, int $tarunaId)
    {
        return $query->where('taruna_id', $tarunaId);
    }

    public function scopeByTanggal($query, string $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }

    public function scopeByPeriode($query, int $bulan, int $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)
                     ->whereYear('tanggal', $tahun);
    }

    // ---------- Accessors ----------

    public function getHasGeolocationAttribute(): bool
    {
        return $this->lat !== null && $this->long !== null;
    }
}
