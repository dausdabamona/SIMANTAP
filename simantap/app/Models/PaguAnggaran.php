<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaguAnggaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pagu_anggaran';

    protected $fillable = [
        'tahun',
        'akun_belanja',
        'nilai_pagu',
        'keterangan',
    ];

    protected $casts = [
        'tahun'      => 'integer',
        'nilai_pagu' => 'decimal:2',
    ];

    // ---------- Scopes ----------

    public function scopeByTahun($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeByAkun($query, string $akun)
    {
        return $query->where('akun_belanja', $akun);
    }
}
