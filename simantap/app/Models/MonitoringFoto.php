<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringFoto extends Model
{
    use HasFactory;

    protected $table = 'monitoring_foto';

    protected $fillable = [
        'penerimaan_id',
        'monitoring_id',
        'file_path',
        'urutan',
        'lat',
        'long',
        'captured_at',
    ];

    protected $casts = [
        'urutan'      => 'integer',
        'lat'         => 'decimal:8',
        'long'        => 'decimal:8',
        'captured_at' => 'datetime',
    ];

    // ---------- Relationships ----------

    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(PenerimaanMakan::class, 'penerimaan_id');
    }

    // ---------- Scopes ----------

    public function scopeByUrutan($query)
    {
        return $query->orderBy('urutan');
    }

    // ---------- Accessors ----------

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
