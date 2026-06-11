<?php

namespace App\Models;

use App\Enums\StatusRekapBulanan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RekapBulanan extends Model
{
    use HasFactory;

    protected $table = 'rekap_bulanan';

    protected $fillable = [
        'periode_bulan', 'periode_tahun', 'taruna_id', 'kontrak_id',
        'total_porsi_diterima', 'harga_porsi_snapshot', 'nilai_bantuan',
        'hari_hadir', 'status', 'catatan',
        'dihitung_oleh', 'dihitung_at', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'status'               => StatusRekapBulanan::class,
        'periode_bulan'        => 'integer',
        'periode_tahun'        => 'integer',
        'total_porsi_diterima' => 'integer',
        'hari_hadir'           => 'integer',
        'harga_porsi_snapshot' => 'decimal:2',
        'nilai_bantuan'        => 'decimal:2',
        'dihitung_at'          => 'datetime',
    ];

    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class);
    }

    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(KontrakMakan::class, 'kontrak_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(RekapBulananApproval::class, 'rekap_bulanan_id');
    }

    public function pengajuanPembayaran(): BelongsToMany
    {
        return $this->belongsToMany(PengajuanPembayaran::class, 'pengajuan_rekap', 'rekap_id', 'pengajuan_id');
    }

    public function dihitungOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dihitung_oleh');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
    }

    public function scopeFinal($query)
    {
        return $query->where('status', StatusRekapBulanan::Final->value);
    }

    public function getPeriodeLabelAttribute(): string
    {
        $dt = \Carbon\Carbon::createFromDate($this->periode_tahun, $this->periode_bulan, 1);
        return $dt->translatedFormat('F Y');
    }

    public function getApprovalPembinaAttribute(): ?RekapBulananApproval
    {
        return $this->approvals->firstWhere('role_approver', 'pembina_karakter');
    }

    public function getApprovalPpkAttribute(): ?RekapBulananApproval
    {
        return $this->approvals->firstWhere('role_approver', 'ppk');
    }

    public function getApprovalKpaAttribute(): ?RekapBulananApproval
    {
        return $this->approvals->firstWhere('role_approver', 'kpa');
    }
}
