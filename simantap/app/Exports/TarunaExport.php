<?php

namespace App\Exports;

use App\Models\Taruna;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TarunaExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Taruna::orderBy('angkatan')->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return ['No', 'NIT', 'Nama', 'NIK', 'Angkatan', 'Prodi', 'Kelas', 'Jenis Kelamin', 'Status', 'Penerima Bantuan', 'Eligible'];
    }

    public function map($t): array
    {
        static $i = 0;
        $i++;
        return [
            $i,
            $t->nit,
            $t->nama,
            $t->nik,
            $t->angkatan,
            $t->prodi,
            $t->kelas,
            $t->jenis_kelamin_label,
            $t->status_taruna_label,
            $t->penerima_bantuan ? 'Ya' : 'Tidak',
            $t->is_eligible_bantuan ? 'Ya' : 'Tidak',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
