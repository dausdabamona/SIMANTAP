<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Append-only — tidak boleh diupdate/dihapus
class WorkflowPembayaran extends Model
{
    use HasFactory;

    protected $table = 'workflow_pembayaran';

    public $timestamps = false;

    protected $fillable = [
        'pengajuan_id', 'user_id', 'status_dari', 'status_ke',
        'aksi', 'catatan', 'ip_address', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanPembayaran::class, 'pengajuan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByPengajuan($query, int $pengajuanId)
    {
        return $query->where('pengajuan_id', $pengajuanId)->orderBy('created_at');
    }
}
