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
        'nomor_rekening',
        'nama_pemilik',
    ];

    // ---------- Relationships ----------

    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class, 'taruna_id');
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
}
