<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SkPenerima extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sk_penerima';

    protected $fillable = [
        'nomor_sk',
        'judul',
        'penerbit',
        'tanggal_sk',
        'periode_mulai',
        'periode_selesai',
        'jenis_sk',
        'file_sk',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_sk'     => 'date',
        'periode_mulai'  => 'date',
        'periode_selesai'=> 'date',
    ];

    // ---------- Scopes ----------

    public function scopeAktifPadaTanggal($query, string $tanggal)
    {
        return $query->where('periode_mulai', '<=', $tanggal)
                     ->where('periode_selesai', '>=', $tanggal);
    }

    public function scopeByJenis($query, string $jenis)
    {
        return $query->where('jenis_sk', $jenis);
    }
}
