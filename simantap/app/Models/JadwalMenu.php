<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JadwalMenu extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jadwal_menu';

    protected $fillable = [
        'kontrak_makan_id',
        'tanggal',
        'jenis_makan',
        'menu_utama',
        'menu_tambahan',
        'menu_buah',
        'menu_minuman',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function kontrakMakan(): BelongsTo
    {
        return $this->belongsTo(KontrakMakan::class);
    }

    public function pemesananHarian(): HasOne
    {
        return $this->hasOne(PemesananHarian::class);
    }

    public function scopeByTanggal($query, string $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }

    public function scopeByJenisMakan($query, string $jenisMakan)
    {
        return $query->where('jenis_makan', $jenisMakan);
    }
}
