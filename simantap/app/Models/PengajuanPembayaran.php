<?php

namespace App\Models;

use App\Enums\StatusPembayaran;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanPembayaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengajuan_pembayaran';

    protected $fillable = [
        'nomor_pengajuan', 'periode_bulan', 'periode_tahun',
        'kontrak_id', 'pagu_anggaran_id',
        'total_taruna', 'total_porsi', 'total_nilai',
        'status', 'catatan',
        'nomor_surat_persetujuan', 'tanggal_surat_persetujuan',
        'nomor_permohonan_kppn', 'tanggal_permohonan_kppn',
        'nomor_sp2d', 'tanggal_sp2d',
        'bukti_transfer_kppn', 'bukti_debit_bank', 'bukti_transfer_penyedia',
        'invoice_penyedia', 'file_lpj', 'file_daftar_ttd_penerima',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'status'                       => StatusPembayaran::class,
        'periode_bulan'                => 'integer',
        'periode_tahun'                => 'integer',
        'total_nilai'                  => 'decimal:2',
        'tanggal_surat_persetujuan'    => 'date',
        'tanggal_permohonan_kppn'      => 'date',
        'tanggal_sp2d'                 => 'date',
    ];

    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(KontrakMakan::class, 'kontrak_id');
    }

    public function paguAnggaran(): BelongsTo
    {
        return $this->belongsTo(PaguAnggaran::class, 'pagu_anggaran_id');
    }

    public function rekapBulanan(): BelongsToMany
    {
        return $this->belongsToMany(RekapBulanan::class, 'pengajuan_rekap', 'pengajuan_id', 'rekap_id');
    }

    public function workflowPembayaran(): HasMany
    {
        return $this->hasMany(WorkflowPembayaran::class, 'pengajuan_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    public function scopePeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
    }

    // Accessors

    public function getPeriodeLabelAttribute(): string
    {
        $bulan = \Carbon\Carbon::createFromDate($this->periode_tahun, $this->periode_bulan, 1);
        return $bulan->translatedFormat('F Y');
    }

    public function getIsSelesaiAttribute(): bool
    {
        return $this->status === StatusPembayaran::Selesai;
    }

    public function getCanTransitionToAttribute(): array
    {
        return $this->status?->allowedTransitions() ?? [];
    }

    /** Auto-generate nomor pengajuan */
    public static function generateNomor(int $bulan, int $tahun): string
    {
        $prefix = config('simantap.nomor_dokumen.pengajuan');
        $count  = static::whereYear('created_at', $tahun)->count() + 1;
        return sprintf('%s/%04d/%03d', $prefix, $tahun, $count);
    }
}
