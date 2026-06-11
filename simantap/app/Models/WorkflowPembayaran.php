<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowPembayaran extends Model
{
    use HasFactory;

    protected $table = 'workflow_pembayaran';

    public $timestamps = false;

    protected $fillable = [
        'pengajuan_id',
        'user_id',
        'status_dari',
        'status_ke',
        'aksi',
        'catatan',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ---------- Relationships ----------

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanPembayaran::class, 'pengajuan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ---------- Scopes ----------

    public function scopeByPengajuan($query, int $pengajuanId)
    {
        return $query->where('pengajuan_id', $pengajuanId);
    }

    public function scopeUrutan($query)
    {
        return $query->orderBy('created_at');
    }
}
