<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prodi extends Model
{
    use HasFactory;

    protected $table = 'prodis';

    protected $fillable = [
        'kode_prodi',
        'nama_prodi',
        'jenjang',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function taruna(): HasMany
    {
        return $this->hasMany(Taruna::class, 'prodi_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }
}
