<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenyediaMakan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penyedia_makan';

    protected $fillable = [
        'nama',
        'npwp',
        'alamat',
        'telp',
        'email',
        'bank',
        'nomor_rekening',
        'nama_pemilik_rekening',
    ];

    // ---------- Relationships ----------

    public function kontrak(): HasMany
    {
        return $this->hasMany(KontrakMakan::class, 'penyedia_id');
    }

    public function kontrakAktif(): HasMany
    {
        return $this->hasMany(KontrakMakan::class, 'penyedia_id')->where('status', 'aktif');
    }
}
