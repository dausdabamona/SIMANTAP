<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekapBulananApproval extends Model
{
    use HasFactory;

    protected $table = 'rekap_bulanan_approval';

    protected $fillable = [
        'rekap_bulanan_id',
        'role',
        'user_id',
        'signed_at',
        'catatan',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    // ---------- Relationships ----------

    public function rekapBulanan(): BelongsTo
    {
        return $this->belongsTo(RekapBulanan::class, 'rekap_bulanan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ---------- Scopes ----------

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
