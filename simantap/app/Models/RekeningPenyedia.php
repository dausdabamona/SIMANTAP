<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekeningPenyedia extends Model
{
    use SoftDeletes;

    protected $table = 'rekening_penyedias';

    protected $fillable = [
        'penyedia_id',
        'label',
        'bank',
        'nomor_rekening',
        'nama_pemilik',
        'is_active',
        'is_default',
        'berlaku_mulai',
        'berlaku_sampai',
        'alasan_perubahan',
        'dibuat_by',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_default'   => 'boolean',
        'berlaku_mulai'  => 'date',
        'berlaku_sampai' => 'date',
    ];

    public function penyedia(): BelongsTo
    {
        return $this->belongsTo(PenyediaMakan::class, 'penyedia_id');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_by');
    }

    // Pastikan hanya satu rekening is_default per penyedia saat simpan
    protected static function booted(): void
    {
        static::saving(function (self $rekening) {
            if ($rekening->is_default && $rekening->isDirty('is_default')) {
                static::where('penyedia_id', $rekening->penyedia_id)
                    ->where('id', '!=', $rekening->id ?? 0)
                    ->update(['is_default' => false]);
            }
        });
    }
}
