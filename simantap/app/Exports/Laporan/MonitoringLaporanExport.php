<?php

namespace App\Exports\Laporan;

use App\Models\SesiPenerimaanMakan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonitoringLaporanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private int $row = 0;

    public function __construct(
        private string $tanggalDari,
        private string $tanggalSampai,
        private ?string $sesi = null,
    ) {}

    public function collection()
    {
        $query = SesiPenerimaanMakan::query()
            ->whereBetween('tanggal', [$this->tanggalDari, $this->tanggalSampai])
            ->orderBy('tanggal')
            ->orderBy('sesi');

        if ($this->sesi && $this->sesi !== 'semua') {
            $query->where('sesi', $this->sesi);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal', 'Sesi', 'Porsi Dipesan', 'Diterima', 'Taruna',
            'Redistribusi', 'Sisa', 'Rekonsiliasi', 'Status',
        ];
    }

    public function map($sesi): array
    {
        $this->row++;
        return [
            $sesi->tanggal?->format('d/m/Y'),
            ucfirst($sesi->sesi),
            $sesi->porsi_dipesan,
            $sesi->porsi_diterima,
            $sesi->porsi_dimakan_taruna,
            $sesi->porsi_redistribusi,
            $sesi->porsi_sisa,
            $sesi->rekonsiliasiValid() ? 'Valid' : 'Tidak Valid',
            $sesi->status_label,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FCE7F3']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Monitoring Sesi';
    }
}
