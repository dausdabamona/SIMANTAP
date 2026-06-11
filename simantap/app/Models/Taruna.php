<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Taruna extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'taruna';

    protected $fillable = [
        'nit',
        'nama',
        'nik',
        'angkatan',
        'prodi',
        'kelas',
        'jenis_kelamin',
        'status_taruna',
        'penerima_bantuan',
    ];

    protected $casts = [
        'angkatan'         => 'integer',
        'penerima_bantuan' => 'boolean',
    ];

    // ---------- Relationships ----------

    public function rekening(): HasOne
    {
        return $this->hasOne(RekeningTaruna::class, 'taruna_id');
    }

    public function penerimaanMakan(): HasMany
    {
        return $this->hasMany(PenerimaanMakan::class, 'taruna_id');
    }

    public function rekapBulanan(): HasMany
    {
        return $this->hasMany(RekapBulanan::class, 'taruna_id');
    }

    public function pemblokiran(): HasMany
    {
        return $this->hasMany(PemblokiranUangMakan::class, 'taruna_id');
    }

    // ---------- Scopes ----------

    public function scopeAktif($query)
    {
        return $query->where('status_taruna', 'aktif');
    }

    public function scopePenerimaBantuan($query)
    {
        return $query->where('penerima_bantuan', true);
    }

    public function scopeEligibleBantuan($query)
    {
        $statusTidakDapat = config('simantap.status_tidak_dapat_bantuan', [
            'cuti', 'pesiar', 'sakit_di_rumah_keluarga', 'penundaan_studi',
        ]);

        return $query->whereNotIn('status_taruna', $statusTidakDapat)
                     ->where('penerima_bantuan', true);
    }

    public function scopeByAngkatan($query, int $angkatan)
    {
        return $query->where('angkatan', $angkatan);
    }

    public function scopeByProdi($query, string $prodi)
    {
        return $query->where('prodi', $prodi);
    }

    // ---------- Accessors ----------

    public function getIsEligibleBantuanAttribute(): bool
    {
        $statusTidakDapat = config('simantap.status_tidak_dapat_bantuan', [
            'cuti', 'pesiar', 'sakit_di_rumah_keluarga', 'penundaan_studi',
        ]);

        return $this->penerima_bantuan && ! in_array($this->status_taruna, $statusTidakDapat);
    }

    public function getJenisKelaminLabelAttribute(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    public function getStatusTarunaLabelAttribute(): string
    {
        return match ($this->status_taruna) {
            'aktif'                    => 'Aktif',
            'cuti'                     => 'Cuti',
            'pesiar'                   => 'Pesiar',
            'sakit_di_kampus'          => 'Sakit di Kampus',
            'sakit_di_rumah_keluarga'  => 'Sakit di Rumah Keluarga',
            'penundaan_studi'          => 'Penundaan Studi',
            default                    => ucfirst($this->status_taruna),
        };
    }
}
