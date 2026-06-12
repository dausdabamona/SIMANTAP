<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePenyedia extends Model
{
    protected $table = 'invoice_penyedias';

    protected $fillable = [
        'periode_bulan', 'periode_tahun', 'penyedia_id',
        'nomor_invoice', 'tanggal_invoice', 'total_nilai',
        'file_invoice', 'status', 'catatan',
        'diverifikasi_by', 'diverifikasi_at',
    ];

    protected $casts = [
        'total_nilai'     => 'decimal:2',
        'tanggal_invoice' => 'date',
        'diverifikasi_at' => 'datetime',
    ];

    const STATUS_MENUNGGU     = 'menunggu';
    const STATUS_DITERIMA     = 'diterima';
    const STATUS_DIVERIFIKASI = 'diverifikasi_ppk';

    // ---------- Relationships ----------

    public function penyedia(): BelongsTo
    {
        return $this->belongsTo(PenyediaMakan::class, 'penyedia_id');
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_by');
    }

    // ---------- Static helpers ----------

    /**
     * Auto-generate invoice setelah kedua transfer BSI + BNI dikonfirmasi.
     * Returns null jika salah satu belum dikonfirmasi.
     */
    public static function generateUntukPeriode(int $bulan, int $tahun): ?self
    {
        $transfers = TransferPenyedia::where([
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
            'status'        => TransferPenyedia::STATUS_DIKONFIRMASI_PENYEDIA,
        ])->get();

        $bankGroups = $transfers->pluck('bank_group')->toArray();
        if (!in_array('BSI', $bankGroups) || !in_array('BNI', $bankGroups)) {
            return null;
        }

        $penyedia = PenyediaMakan::whereHas('kontrakAktif')->firstOrFail();

        return self::firstOrCreate(
            ['periode_bulan' => $bulan, 'periode_tahun' => $tahun, 'penyedia_id' => $penyedia->id],
            [
                'total_nilai' => $transfers->sum('total_nilai'),
                'status'      => self::STATUS_MENUNGGU,
            ]
        );
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
            self::STATUS_MENUNGGU     => 'Menunggu Upload',
            self::STATUS_DITERIMA     => 'Diterima',
            self::STATUS_DIVERIFIKASI => 'Diverifikasi PPK',
            default                   => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU     => 'secondary',
            self::STATUS_DITERIMA     => 'warning',
            self::STATUS_DIVERIFIKASI => 'success',
            default                   => 'secondary',
        };
    }
}
