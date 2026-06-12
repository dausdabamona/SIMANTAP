<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekeningTaruna extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rekening_taruna';

    protected $fillable = [
        'taruna_id',
        'bank',
        'bank_group',
        'senat_account_id',
        'nomor_rekening',
        'nama_pemilik',
    ];

    // ---------- Hooks ----------

    protected static function booted(): void
    {
        static::creating(function (RekeningTaruna $rek) {
            $bankUpper = strtoupper($rek->bank ?? '');
            $rek->bank_group = str_contains($bankUpper, 'BSI') ? 'BSI'
                : (str_contains($bankUpper, 'BNI') ? 'BNI'
                : (str_contains($bankUpper, 'MANDIRI') ? 'MANDIRI'
                : (str_contains($bankUpper, 'BRI') ? 'BRI' : 'LAINNYA')));

            if (!$rek->senat_account_id) {
                $tingkat = $rek->taruna?->tingkat_taruna ?? 2;
                $bankSenat = $tingkat === 1 ? 'BSI' : 'BNI';
                $rek->senat_account_id = SenatAccount::where('bank_group', $bankSenat)
                    ->where('is_aktif', true)
                    ->value('id');
            }
        });
    }

    // ---------- Relationships ----------

    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class, 'taruna_id');
    }

    public function senatAccount(): BelongsTo
    {
        return $this->belongsTo(SenatAccount::class, 'senat_account_id');
    }

    // ---------- Accessors ----------

    public function getNomorRekeningMaskedAttribute(): string
    {
        $nomor = $this->nomor_rekening;
        $len   = strlen($nomor);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        return str_repeat('*', $len - 4) . substr($nomor, -4);
    }

    public function getTingkatTarunaAttribute(): int
    {
        $tahunMasuk = $this->taruna?->angkatan ?? now()->year;
        $tahunSekarang = now()->year;
        $bulanSekarang = now()->month;
        $tingkat = ($bulanSekarang >= 8)
            ? ($tahunSekarang - $tahunMasuk + 1)
            : ($tahunSekarang - $tahunMasuk);
        return max(1, min(3, $tingkat));
    }
}
