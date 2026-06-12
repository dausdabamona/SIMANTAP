<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SenatAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'senat_accounts';

    protected $fillable = [
        'nama_akun',
        'bank',
        'bank_group',
        'untuk_tingkat',
        'nomor_rekening',
        'nama_pemilik',
        'is_aktif',
        'keterangan',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    // ---------- Relationships ----------

    public function pemblokiran(): HasMany
    {
        return $this->hasMany(PemblokiranUangMakan::class, 'senat_account_id');
    }

    public function rekeningTaruna(): HasMany
    {
        return $this->hasMany(RekeningTaruna::class, 'senat_account_id');
    }

    public function pengajuanPembayaran(): HasMany
    {
        return $this->hasMany(PengajuanPembayaran::class, 'rekening_senat_id');
    }

    // ---------- Scopes ----------

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    /** Satu rekening aktif per bank_group */
    public function scopeAktifPerGroup($query, string $bankGroup)
    {
        return $query->where('bank_group', $bankGroup)->where('is_aktif', true);
    }

    // ---------- Helpers ----------

    /** Deteksi bank_group dari nama bank */
    public static function detectBankGroup(string $bank): string
    {
        $upper = strtoupper($bank);
        return str_contains($upper, 'BSI') ? 'BSI'
            : (str_contains($upper, 'BNI') ? 'BNI'
            : (str_contains($upper, 'MANDIRI') ? 'MANDIRI'
            : (str_contains($upper, 'BRI') ? 'BRI'
            : 'LAINNYA')));
    }
}
