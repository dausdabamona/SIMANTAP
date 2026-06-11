<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranLuarKampus extends Model
{
    use SoftDeletes;

    protected $table = 'pembayaran_luar_kampus';

    protected $fillable = [
        'kegiatan_id', 'tahap', 'nilai_diajukan', 'nilai_disetujui',
        'nomor_sp2d', 'tanggal_sp2d', 'file_sp2d', 'status', 'catatan',
    ];

    protected $casts = [
        'tanggal_sp2d'   => 'date',
        'nilai_diajukan' => 'decimal:2',
        'nilai_disetujui'=> 'decimal:2',
    ];

    const STATUS_DRAFT                = 'draft';
    const STATUS_DIVERIFIKASI_PPK     = 'diverifikasi_ppk';
    const STATUS_DIAJUKAN_KPPN        = 'diajukan_kppn';
    const STATUS_SP2D_TERBIT          = 'sp2d_terbit';
    const STATUS_TRANSFER_SELESAI     = 'transfer_selesai';
    const STATUS_DIKONFIRMASI_TARUNA  = 'dikonfirmasi_taruna';

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(KegiatanLuarKampus::class, 'kegiatan_id');
    }
}
