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
    private int $row = 0;

    public function collection()
    {
        return Taruna::with('prodi')
            ->orderBy('angkatan')
            ->orderBy('kelas')
            ->orderBy('nama')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No', 'NIT', 'Nama', 'NIK', 'Angkatan',
            'Kode Prodi', 'Nama Prodi', 'Kelas',
            'Jenis Kelamin', 'Status', 'Penerima Bantuan', 'Eligible Bantuan',
        ];
    }

    public function map($t): array
    {
        $this->row++;
        $prodiModel = $t->relationLoaded('prodi') ? $t->getRelation('prodi') : null;
        return [
            $this->row,
            $t->nit,
            $t->nama,
            $t->nik ?? '-',
            $t->angkatan,
            $prodiModel?->kode_prodi ?? $t->getAttribute('prodi'),
            $prodiModel?->nama_prodi ?? $t->getAttribute('prodi'),
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
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D1FAE5']],
            ],
        ];
    }
}
