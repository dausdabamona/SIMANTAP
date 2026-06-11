<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemblokiranUangMakan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pemblokiran_uang_makan';

    protected $fillable = [
        'periode_bulan',
        'periode_tahun',
        'taruna_id',
        'status',
        'nomor_surat',
        'bukti_debit',
        'target_rekening_id',
        'jumlah',
        'keterangan',
    ];

    protected $casts = [
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'jumlah'        => 'decimal:2',
    ];

    const STATUS_DIUSULKAN = 'diusulkan';
    const STATUS_DIBLOKIR  = 'diblokir';
    const STATUS_DIDEBIT   = 'didebit';

    // ---------- Relationships ----------

    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class, 'taruna_id');
    }

    public function targetRekening(): BelongsTo
    {
        return $this->belongsTo(SenatAccount::class, 'target_rekening_id');
    }

    // ---------- Scopes ----------

    public function scopeDisusulkan($query)
    {
        return $query->where('status', self::STATUS_DIUSULKAN);
    }

    public function scopeDiblokir($query)
    {
        return $query->where('status', self::STATUS_DIBLOKIR);
    }

    public function scopeByPeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
    }
}
