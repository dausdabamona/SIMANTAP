<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TransferPenyedia extends Model
{
    protected $table = 'transfer_penyedias';

    protected $fillable = [
        'periode_bulan', 'periode_tahun', 'bank_group',
        'senat_account_id', 'rekening_penyedia_id',
        'total_nilai', 'jumlah_kelas', 'jumlah_taruna',
        'tanggal_transfer', 'bukti_transfer', 'status', 'catatan',
        'disetujui_wadir_by', 'disetujui_wadir_at',
        'ditransfer_by', 'dikonfirmasi_by', 'dikonfirmasi_at',
    ];

    protected $casts = [
        'total_nilai'         => 'decimal:2',
        'tanggal_transfer'    => 'date',
        'disetujui_wadir_at'  => 'datetime',
        'dikonfirmasi_at'     => 'datetime',
    ];

    const STATUS_MENUNGGU              = 'menunggu';
    const STATUS_DISETUJUI_WADIR       = 'disetujui_wadir';
    const STATUS_DITRANSFER            = 'ditransfer';
    const STATUS_DIKONFIRMASI_PENYEDIA = 'dikonfirmasi_penyedia';

    // ---------- Relationships ----------

    public function senatAccount(): BelongsTo
    {
        return $this->belongsTo(SenatAccount::class, 'senat_account_id');
    }

    public function rekeningPenyedia(): BelongsTo
    {
        return $this->belongsTo(RekeningPenyedia::class, 'rekening_penyedia_id');
    }

    public function spms(): BelongsToMany
    {
        return $this->belongsToMany(
            PengajuanPembayaran::class,
            'transfer_penyedia_spms',
            'transfer_penyedia_id',
            'pengajuan_id'
        )->withPivot('nilai_kontribusi')->withTimestamps();
    }

    public function disetujuiWadirOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_wadir_by');
    }

    public function ditransferOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditransfer_by');
    }

    public function dikonfirmasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_by');
    }

    // ---------- Scopes ----------

    public function scopePeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
    }

    // ---------- Static helpers ----------

    /**
     * Generate (or update) TransferPenyedia untuk satu bank_group periode,
     * dipanggil setelah semua SPM bank_group itu berstatus debit_selesai.
     */
    public static function generateUntukPeriode(int $bulan, int $tahun, string $bankGroup): self
    {
        $spms = PengajuanPembayaran::where([
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
            'bank_group'    => $bankGroup,
            'status'        => PengajuanPembayaran::STATUS_DEBIT_SELESAI,
        ])->get();

        $senat = SenatAccount::where('bank_group', $bankGroup)
            ->where('is_aktif', true)
            ->firstOrFail();

        $rekPenyedia = RekeningPenyedia::where('is_default', true)
            ->where('is_active', true)
            ->firstOrFail();

        $transfer = self::firstOrCreate(
            ['periode_bulan' => $bulan, 'periode_tahun' => $tahun, 'bank_group' => $bankGroup],
            [
                'senat_account_id'     => $senat->id,
                'rekening_penyedia_id' => $rekPenyedia->id,
                'total_nilai'          => $spms->sum('total_nilai'),
                'jumlah_kelas'         => $spms->count(),
                'jumlah_taruna'        => $spms->sum('total_taruna'),
                'status'               => self::STATUS_MENUNGGU,
            ]
        );

        foreach ($spms as $spm) {
            $transfer->spms()->syncWithoutDetaching([
                $spm->id => ['nilai_kontribusi' => $spm->total_nilai],
            ]);
        }

        return $transfer;
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
            self::STATUS_MENUNGGU              => 'Menunggu Persetujuan',
            self::STATUS_DISETUJUI_WADIR       => 'Disetujui Wadir III',
            self::STATUS_DITRANSFER            => 'Sudah Ditransfer',
            self::STATUS_DIKONFIRMASI_PENYEDIA => '✓ Dikonfirmasi Penyedia',
            default                            => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU              => 'warning',
            self::STATUS_DISETUJUI_WADIR       => 'info',
            self::STATUS_DITRANSFER            => 'primary',
            self::STATUS_DIKONFIRMASI_PENYEDIA => 'success',
            default                            => 'secondary',
        };
    }
}
