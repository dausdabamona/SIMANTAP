<?php

namespace App\Exports\Laporan;

use App\Models\Taruna;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TarunaLaporanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private int $row = 0;

    public function __construct(
        private int $tahun,
        private ?string $angkatan = null,
        private ?string $prodi = null,
        private ?string $status_taruna = null,
    ) {}

    public function collection()
    {
        $query = Taruna::query();

        if ($this->angkatan) {
            $query->where('angkatan', $this->angkatan);
        }
        if ($this->prodi) {
            $query->where('prodi', $this->prodi);
        }
        if ($this->status_taruna) {
            $query->where('status_taruna', $this->status_taruna);
        }

        return $query->orderBy('angkatan')->orderBy('kelas')->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return [
            'No', 'NIT', 'Nama', 'Angkatan', 'Prodi', 'Kelas',
            'Jenis Kelamin', 'Status', 'Penerima Bantuan', 'Eligible',
        ];
    }

    public function map($t): array
    {
        $this->row++;
        return [
            $this->row,
            $t->nit,
            $t->nama,
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
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D1FAE5']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Data Taruna';
    }
}
