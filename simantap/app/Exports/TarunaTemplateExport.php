<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TarunaTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['20230001', 'Budi Santoso', '3271012345678901', 2023, 'Teknologi Penangkapan Ikan', 'TPI-1A', 'L', 'aktif', 'ya'],
        ];
    }

    public function headings(): array
    {
        return ['nit', 'nama', 'nik', 'angkatan', 'prodi', 'kelas', 'jenis_kelamin', 'status_taruna', 'penerima_bantuan'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D1FAE5']]],
        ];
    }
}
