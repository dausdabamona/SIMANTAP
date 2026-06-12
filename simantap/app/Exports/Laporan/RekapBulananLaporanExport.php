<?php

namespace App\Exports\Laporan;

use App\Models\RekapBulanan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapBulananLaporanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private int $row = 0;

    public function __construct(
        private int $bulan,
        private int $tahun,
        private ?string $kontrak_id = null,
    ) {}

    public function collection()
    {
        $query = RekapBulanan::with('taruna')
            ->where('periode_bulan', $this->bulan)
            ->where('periode_tahun', $this->tahun);

        if ($this->kontrak_id) {
            $query->where('kontrak_id', $this->kontrak_id);
        }

        return $query->orderBy('id')->get();
    }

    public function headings(): array
    {
        return [
            'No', 'NIT', 'Nama', 'Kelas', 'Total Sesi', 'Hari Hadir', 'Nilai Bantuan',
        ];
    }

    public function map($rekap): array
    {
        $this->row++;
        return [
            $this->row,
            $rekap->taruna?->nit ?? '-',
            $rekap->taruna?->nama ?? '-',
            $rekap->taruna?->kelas ?? '-',
            $rekap->total_sesi,
            $rekap->hari_hadir,
            $rekap->nilai_bantuan,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DBEAFE']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Rekap Bulanan';
    }
}
