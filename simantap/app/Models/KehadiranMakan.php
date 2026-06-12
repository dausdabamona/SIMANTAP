<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KehadiranMakan extends Model
{
    protected $table = 'kehadiran_makans';

    protected $fillable = [
        'sesi_id', 'taruna_id', 'hadir',
        'sumber', 'waktu_scan', 'diinput_by', 'keterangan',
    ];

    protected $casts = [
        'hadir'      => 'boolean',
        'waktu_scan' => 'datetime',
    ];

    const SUMBER_FINGERPRINT = 'fingerprint';
    const SUMBER_SCAN_NIT    = 'scan_nit';
    const SUMBER_MANUAL      = 'manual';

    // ── Relationships ────────────────────────────────────────

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiPenerimaanMakan::class, 'sesi_id');
    }

    public function taruna(): BelongsTo
    {
        return $this->belongsTo(Taruna::class, 'taruna_id');
    }

    public function diinputOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diinput_by');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeHadir($query)
    {
        return $query->where('hadir', true);
    }
}
