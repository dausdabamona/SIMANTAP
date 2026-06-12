<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TarunaTemplateSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Template Import';
    }

    public function headings(): array
    {
        return [
            'nit', 'nama', 'nik', 'angkatan',
            'kode_prodi', 'kelas', 'jenis_kelamin',
            'status_taruna', 'penerima_bantuan',
        ];
    }

    public function array(): array
    {
        return [
            ['20230001', 'Ahmad Fauzi',  '3271012345678901', 2023, 'TPI', 'X-A',  'L', 'aktif', 'ya'],
            ['20230002', 'Sari Dewi',    '3271019876543210', 2023, 'NKL', 'X-A',  'P', 'aktif', 'ya'],
            ['20220001', 'Budi Hartono', '3271015678901234', 2022, 'TPI', 'XI-B', 'L', 'aktif', 'ya'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, 'B' => 30, 'C' => 20, 'D' => 12,
            'E' => 12, 'F' => 10, 'G' => 8,  'H' => 25, 'I' => 18,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Insert petunjuk singkat di baris 2, geser data ke baris 3+
        $sheet->insertNewRowBefore(2);
        $sheet->setCellValue('A2', '← NIT unik');
        $sheet->setCellValue('B2', '← Nama lengkap');
        $sheet->setCellValue('C2', '← NIK 16 digit (opsional)');
        $sheet->setCellValue('D2', '← Tahun masuk (mis. 2023)');
        $sheet->setCellValue('E2', '← Kode prodi (lihat sheet Referensi Prodi)');
        $sheet->setCellValue('F2', '← Kelas (X-A, XI-B, XII-A, dst)');
        $sheet->setCellValue('G2', '← L atau P');
        $sheet->setCellValue('H2', '← aktif / cuti / pesiar / sakit_di_kampus / sakit_di_rumah_keluarga / penundaan_studi');
        $sheet->setCellValue('I2', '← ya / tidak');

        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D1FAE5']],
            ],
            2 => [
                'font' => ['italic' => true, 'color' => ['rgb' => '6B7280']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEF9C3']],
            ],
        ];
    }
}
