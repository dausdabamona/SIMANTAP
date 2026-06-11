<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalMenu extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jadwal_menu';

    protected $fillable = [
        'kontrak_id', 'tanggal', 'jenis_makan', 'menu',
        'porsi_per_taruna', 'catatan', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'tanggal'          => 'date',
        'porsi_per_taruna' => 'integer',
    ];

    const JENIS = ['sarapan', 'makan_siang', 'makan_malam'];

    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(KontrakMakan::class, 'kontrak_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByKontrakTanggal($query, int $kontrakId, string $tanggal)
    {
        return $query->where('kontrak_id', $kontrakId)->where('tanggal', $tanggal);
    }

    public function getJenisMakanLabelAttribute(): string
    {
        return match ($this->jenis_makan) {
            'sarapan'     => 'Sarapan',
            'makan_siang' => 'Makan Siang',
            'makan_malam' => 'Makan Malam',
            default       => ucfirst($this->jenis_makan),
        };
    }
}
