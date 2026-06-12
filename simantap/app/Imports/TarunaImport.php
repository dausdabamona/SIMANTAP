<?php

namespace App\Imports;

use App\Models\Prodi;
use App\Models\Taruna;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class TarunaImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure, WithSkipDuplicates
{
    use \Maatwebsite\Excel\Concerns\Importable;

    private array $prodiCache = [];
    private array $failures   = [];

    public function model(array $row): ?Taruna
    {
        $kodeProdi = strtoupper(trim($row['kode_prodi'] ?? ''));
        if (!isset($this->prodiCache[$kodeProdi])) {
            $this->prodiCache[$kodeProdi] = Prodi::where('kode_prodi', $kodeProdi)
                ->orWhere('nama_prodi', trim($row['kode_prodi'] ?? ''))
                ->value('id');
        }
        $prodiId = $this->prodiCache[$kodeProdi];

        return new Taruna([
            'nit'              => trim($row['nit']),
            'nama'             => trim($row['nama']),
            'nik'              => isset($row['nik']) && $row['nik'] !== '' ? trim($row['nik']) : null,
            'angkatan'         => (int) $row['angkatan'],
            'prodi_id'         => $prodiId,
            'prodi'            => $kodeProdi,
            'kelas'            => strtoupper(trim($row['kelas'])),
            'jenis_kelamin'    => strtoupper(trim($row['jenis_kelamin'])),
            'status_taruna'    => strtolower(trim($row['status_taruna'] ?? 'aktif')),
            'penerima_bantuan' => in_array(
                strtolower(trim($row['penerima_bantuan'] ?? 'ya')),
                ['ya', '1', 'true', 'yes']
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'nit'              => 'required|string|unique:taruna,nit',
            'nama'             => 'required|string|max:100',
            'angkatan'         => 'required|integer|min:2000|max:2099',
            'kode_prodi'       => 'required|string',
            'kelas'            => 'required|string|max:20',
            'jenis_kelamin'    => 'required|in:L,P',
            'status_taruna'    => 'nullable|in:aktif,cuti,pesiar,sakit_di_kampus,sakit_di_rumah_keluarga,penundaan_studi',
            'penerima_bantuan' => 'nullable',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nit.unique'        => 'NIT :input sudah terdaftar di sistem.',
            'jenis_kelamin.in'  => 'Jenis kelamin harus L atau P.',
            'status_taruna.in'  => 'Status tidak valid. Pilihan: aktif, cuti, pesiar, sakit_di_kampus, sakit_di_rumah_keluarga, penundaan_studi.',
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->failures[] = $failure;
        }
    }

    public function failures(): \Illuminate\Support\Collection
    {
        return collect($this->failures);
    }
}
