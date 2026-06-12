<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PenyediaMakan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penyedia_makan';

    protected $fillable = [
        'user_id',
        'nama',
        'npwp',
        'alamat',
        'telp',
        'email',
    ];

    // ---------- Relationships ----------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kontrak(): HasMany
    {
        return $this->hasMany(KontrakMakan::class, 'penyedia_id');
    }

    public function kontrakAktif(): HasMany
    {
        return $this->hasMany(KontrakMakan::class, 'penyedia_id')->where('status', 'aktif');
    }

    public function rekening(): HasMany
    {
        return $this->hasMany(RekeningPenyedia::class, 'penyedia_id');
    }

    /** Rekening aktif yang ditandai default — dipakai untuk pembayaran baru */
    public function rekeningDefault(): HasOne
    {
        return $this->hasOne(RekeningPenyedia::class, 'penyedia_id')
            ->where('is_default', true)
            ->where('is_active', true);
    }

    /** Semua rekening yang masih aktif */
    public function rekeningAktif(): HasMany
    {
        return $this->hasMany(RekeningPenyedia::class, 'penyedia_id')
            ->where('is_active', true);
    }
}
