<?php

namespace App\Models;

use App\Enums\StatusPemblokiran;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemblokiranUangMakan extends Model
{
    use HasFactory;

    protected $table = 'pemblokiran_uang_makan';

    protected $fillable = [
        'periode_bulan', 'periode_tahun', 'taruna_id', 'senat_account_id',
        'nilai_bantuan', 'status',
        'nomor_surat_pemblokiran', 'tanggal_surat', 'file_surat_pemblokiran',
        'bukti_debit_bank', 'tanggal_debit', 'nilai_didebit', 'catatan',
        'diusulkan_oleh', 'diusulkan_at', 'diproses_oleh', 'diproses_at',
        'created_by',
    ];

    protected $casts = [
        'status'         => StatusPemblokiran::class,
        'nilai_bantuan'  => 'decimal:2',
        'nilai_didebit'  => 'decimal:2',
        'tanggal_surat'  => 'date',
        'tanggal_debit'  => 'datetime',
        'diusulkan_at'   => 'datetime',
        'diproses_at'    => 'datetime',
    ];

    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class);
    }

    public function senatAccount(): BelongsTo
    {
        return $this->belongsTo(SenatAccount::class, 'senat_account_id');
    }

    public function diusulkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diusulkan_oleh');
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function getPeriodeLabelAttribute(): string
    {
        $dt = \Carbon\Carbon::createFromDate($this->periode_tahun, $this->periode_bulan, 1);
        return $dt->translatedFormat('F Y');
    }
}
