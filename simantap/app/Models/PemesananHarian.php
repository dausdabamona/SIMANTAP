<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PemesananHarian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pemesanan_harian';

    protected $fillable = [
        'tanggal', 'kontrak_id', 'jumlah_taruna_hadir', 'jumlah_porsi',
        'harga_porsi_snapshot', 'nilai_total',
        'catatan_menu', 'menu_sesuai_jadwal',
        'status', 'catatan',
        'ttd_senat_id', 'ttd_senat_at',
        'ttd_pembina_id', 'ttd_pembina_at', 'catatan_pembina',
        'dikirim_at', 'dikirim_oleh',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'tanggal'            => 'date',
        'harga_porsi_snapshot' => 'decimal:2',
        'nilai_total'        => 'decimal:2',
        'menu_sesuai_jadwal' => 'boolean',
        'ttd_senat_at'       => 'datetime',
        'ttd_pembina_at'     => 'datetime',
        'dikirim_at'         => 'datetime',
    ];

    const STATUS_DRAFT               = 'draft';
    const STATUS_DIVERIFIKASI_PEMBINA = 'diverifikasi_pembina';
    const STATUS_DIKIRIM_PENYEDIA    = 'dikirim_penyedia';
    const STATUS_PERUBAHAN           = 'perubahan';
    const STATUS_DISAJIKAN           = 'disajikan';
    const STATUS_SELESAI             = 'selesai';

    // ---------- Relationships ----------

    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(KontrakMakan::class, 'kontrak_id');
    }

    public function ttdSenat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ttd_senat_id');
    }

    public function ttdPembina(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ttd_pembina_id');
    }

    public function dikirimOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }

    public function penerimaanMakan(): HasMany
    {
        return $this->hasMany(PenerimaanMakan::class, 'pemesanan_id');
    }

    public function monitoringFoto(): HasMany
    {
        return $this->hasMany(MonitoringFoto::class, 'pemesanan_id');
    }

    public function beritaAcaraPerubahan(): HasMany
    {
        return $this->hasMany(BeritaAcaraPerubahan::class, 'pemesanan_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Jadwal menu rencana untuk tanggal ini (Level 1) */
    public function jadwalMenu(): HasMany
    {
        return $this->hasMany(JadwalMenu::class, 'kontrak_id', 'kontrak_id')
            ->where('tanggal', $this->tanggal);
    }

    // ---------- Scopes ----------

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeTanggal($query, string $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }

    // ---------- Business Logic ----------

    /** Hitung ulang nilai total berdasarkan porsi × harga snapshot */
    public function hitungNilai(): void
    {
        $porsiPerHari         = config('simantap.porsi_per_hari', 3);
        $this->jumlah_porsi   = $this->jumlah_taruna_hadir * $porsiPerHari;
        $this->nilai_total    = $this->jumlah_porsi * $this->harga_porsi_snapshot;
    }

    /** Apakah masih dalam batas waktu kirim (H-1 sebelum jam batas) */
    public function getIsDeadlineKirimAttribute(): bool
    {
        $batas     = config('simantap.makan.batas_jam_pesan', '14:00');
        $deadlineH1 = \Carbon\Carbon::parse($this->tanggal)
            ->subDay()
            ->setTimeFromTimeString($batas);
        return now()->greaterThan($deadlineH1);
    }

    /** Perubahan setelah deadline wajib Berita Acara */
    public function getWajibBeritaAcaraAttribute(): bool
    {
        return $this->status === self::STATUS_DIKIRIM_PENYEDIA
            && $this->is_deadline_kirim;
    }
}
