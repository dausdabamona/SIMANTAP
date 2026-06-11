<?php

namespace App\Imports;

use App\Models\Taruna;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class TarunaImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function model(array $row): ?Taruna
    {
        return new Taruna([
            'nit'              => trim($row['nit']),
            'nama'             => trim($row['nama']),
            'nik'              => isset($row['nik']) ? trim($row['nik']) : null,
            'angkatan'         => (int) $row['angkatan'],
            'prodi'            => trim($row['prodi']),
            'kelas'            => trim($row['kelas']),
            'jenis_kelamin'    => strtoupper(trim($row['jenis_kelamin'])),
            'status_taruna'    => strtolower(trim($row['status_taruna'] ?? 'aktif')),
            'penerima_bantuan' => in_array(strtolower(trim($row['penerima_bantuan'] ?? 'ya')), ['ya', '1', 'true', 'yes']),
        ]);
    }

    public function rules(): array
    {
        return [
            'nit'           => 'required|string|unique:taruna,nit',
            'nama'          => 'required|string',
            'angkatan'      => 'required|integer',
            'prodi'         => 'required|string',
            'kelas'         => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'status_taruna' => 'nullable|in:aktif,cuti,pesiar,sakit_di_kampus,sakit_di_rumah_keluarga,penundaan_studi',
        ];
    }
}
