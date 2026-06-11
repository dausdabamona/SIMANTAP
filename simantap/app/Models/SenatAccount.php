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
        return $this->hasMany(PemblokiranUangMakan::class, 'target_rekening_id');
    }

    // ---------- Scopes ----------

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }
}
