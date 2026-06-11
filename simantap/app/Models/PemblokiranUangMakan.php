<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemblokiranUangMakan extends Model
{
    use HasFactory;
    // Tidak pakai SoftDeletes — dokumen keuangan immutable

    protected $table = 'pemblokiran_uang_makan';

    protected $fillable = [
        'periode_bulan', 'periode_tahun', 'taruna_id',
        // Koreksi #4: senat_account_id (target debit otomatis bank)
        'senat_account_id',
        'nilai_bantuan', 'status',
        'nomor_surat_pemblokiran', 'tanggal_surat', 'file_surat_pemblokiran',
        // Koreksi #4: bukti_debit_bank + tanggal_debit + nilai_didebit
        'bukti_debit_bank', 'tanggal_debit', 'nilai_didebit',
        'catatan',
        'diusulkan_oleh', 'diusulkan_at',
        'diproses_oleh', 'diproses_at',
        'created_by',
    ];

    protected $casts = [
        'nilai_bantuan'  => 'decimal:2',
        'nilai_didebit'  => 'decimal:2',
        'tanggal_surat'  => 'date',
        'tanggal_debit'  => 'datetime',
        'diusulkan_at'   => 'datetime',
        'diproses_at'    => 'datetime',
    ];

    const STATUS_DIUSULKAN = 'diusulkan';
    const STATUS_DIBLOKIR  = 'diblokir';
    const STATUS_DIDEBIT   = 'didebit';

    // ---------- Relationships ----------

    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class);
    }

    /** Rekening Senat yang menerima debit otomatis dari bank */
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

    // ---------- Scopes ----------

    public function scopePeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ---------- Accessors ----------

    public function getPeriodeLabelAttribute(): string
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April',   5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',   9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return ($bulan[$this->periode_bulan] ?? '-') . ' ' . $this->periode_tahun;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DIUSULKAN => 'Diusulkan',
            self::STATUS_DIBLOKIR  => 'Diblokir',
            self::STATUS_DIDEBIT   => 'Didebit ke Senat',
            default                => ucfirst($this->status),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DIUSULKAN => 'badge bg-warning text-dark',
            self::STATUS_DIBLOKIR  => 'badge bg-info text-dark',
            self::STATUS_DIDEBIT   => 'badge bg-success',
            default                => 'badge bg-secondary',
        };
    }
}
