<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenyediaMakan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penyedia_makan';

    protected $fillable = [
        'nama_penyedia', 'npwp', 'alamat', 'telepon', 'email',
        'bank', 'rekening', 'nama_pemilik_rekening',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kontrakMakan(): HasMany
    {
        return $this->hasMany(KontrakMakan::class, 'penyedia_id');
    }

    public function kontrakAktif(): HasMany
    {
        return $this->hasMany(KontrakMakan::class, 'penyedia_id')->where('status', 'aktif');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }
}
