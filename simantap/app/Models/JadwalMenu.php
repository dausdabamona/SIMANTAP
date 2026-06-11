<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalMenu extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jadwal_menu';

    protected $fillable = [
        'tanggal',
        'jenis_makan',
        'menu',
        'nilai_gizi',
        'porsi_per_taruna',
    ];

    protected $casts = [
        'tanggal'          => 'date',
        'porsi_per_taruna' => 'integer',
    ];

    // ---------- Scopes ----------

    public function scopeByTanggal($query, string $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }

    public function scopeByJenisMakan($query, string $jenis)
    {
        return $query->where('jenis_makan', $jenis);
    }

    public function scopeByPeriode($query, string $dari, string $sampai)
    {
        return $query->whereBetween('tanggal', [$dari, $sampai]);
    }
}
