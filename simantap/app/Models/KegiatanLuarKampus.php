<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KegiatanLuarKampus extends Model
{
    use SoftDeletes;

    protected $table = 'kegiatan_luar_kampus';

    protected $fillable = [
        'kode_kegiatan', 'nama_kegiatan', 'deskripsi',
        'tanggal_mulai', 'tanggal_selesai', 'lokasi', 'jenis_kegiatan',
        'kaprodi_id', 'standar_biaya_per_hari',
        'total_nilai_diusulkan', 'total_nilai_disetujui',
        'nomor_surat_pusdik', 'tanggal_surat_pusdik', 'file_surat_pusdik',
        'status', 'catatan_penolakan',
    ];

    protected $casts = [
        'tanggal_mulai'          => 'date',
        'tanggal_selesai'        => 'date',
        'tanggal_surat_pusdik'   => 'date',
        'standar_biaya_per_hari' => 'decimal:2',
        'total_nilai_diusulkan'  => 'decimal:2',
        'total_nilai_disetujui'  => 'decimal:2',
    ];

    // Status constants
    const STATUS_DRAFT                        = 'draft';
    const STATUS_DIUSULKAN_KAPRODI            = 'diusulkan_kaprodi';
    const STATUS_DISETUJUI_DIREKTUR           = 'disetujui_direktur';
    const STATUS_MENUNGGU_PERSETUJUAN_PUSDIK  = 'menunggu_persetujuan_pusdik';
    const STATUS_DISETUJUI_PUSDIK             = 'disetujui_pusdik';
    const STATUS_PROSES_PEMBAYARAN            = 'proses_pembayaran';
    const STATUS_SELESAI                      = 'selesai';
    const STATUS_DIBATALKAN                   = 'dibatalkan';

    public function kaprodi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kaprodi_id');
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(PesertaKegiatanLuarKampus::class, 'kegiatan_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(PembayaranLuarKampus::class, 'kegiatan_id');
    }

    public function totalDibayar(): float
    {
        return (float) $this->pembayaran()->whereIn('status', [
            PembayaranLuarKampus::STATUS_SP2D_TERBIT,
            PembayaranLuarKampus::STATUS_TRANSFER_SELESAI,
            PembayaranLuarKampus::STATUS_DIKONFIRMASI_TARUNA,
        ])->sum('nilai_disetujui');
    }

    public function sisaAnggaranTersedia(): float
    {
        return (float) $this->total_nilai_disetujui - $this->totalDibayar();
    }

    public static function generateKode(int $tahun): string
    {
        $last = static::withTrashed()
            ->whereYear('created_at', $tahun)
            ->lockForUpdate()
            ->count();
        return sprintf('KLK/%d/%03d', $tahun, $last + 1);
    }
}
