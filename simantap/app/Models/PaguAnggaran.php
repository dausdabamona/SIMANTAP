<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaguAnggaran extends Model
{
    use HasFactory;

    protected $table = 'pagu_anggaran';

    protected $fillable = [
        'tahun',
        'total_pagu',
        'terpakai',
        'sisa',
        'kode_akun',
        'kode_kegiatan',
        'keterangan',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'total_pagu' => 'decimal:2',
        'terpakai' => 'decimal:2',
        'sisa' => 'decimal:2',
    ];

    public function scopeByTahun($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function getSisaPersenAttribute(): float
    {
        if ($this->total_pagu == 0) return 0;
        return round(($this->sisa / $this->total_pagu) * 100, 2);
    }

    public function getTerpakaiPersenAttribute(): float
    {
        if ($this->total_pagu == 0) return 0;
        return round(($this->terpakai / $this->total_pagu) * 100, 2);
    }
}
