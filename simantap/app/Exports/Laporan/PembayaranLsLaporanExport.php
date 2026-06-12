<?php

namespace App\Exports\Laporan;

use App\Models\InvoicePenyedia;
use App\Models\PengajuanPembayaran;
use App\Models\TransferPenyedia;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PembayaranLsLaporanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private array $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function __construct(private int $tahun) {}

    public function collection(): Collection
    {
        $pengajuanAll = PengajuanPembayaran::where('periode_tahun', $this->tahun)->get();
        $transferAll  = TransferPenyedia::where('periode_tahun', $this->tahun)->get();
        $invoiceAll   = InvoicePenyedia::where('periode_tahun', $this->tahun)->get();

        $rows = collect();
        for ($m = 1; $m <= 12; $m++) {
            $rows->push([
                'bulan'                   => $this->namaBulan[$m],
                'jumlah_pengajuan'        => $pengajuanAll->where('periode_bulan', $m)->count(),
                'total_nilai_pengajuan'   => $pengajuanAll->where('periode_bulan', $m)->sum('total_nilai'),
                'total_transfer_penyedia' => $transferAll->where('periode_bulan', $m)->sum('total_nilai'),
                'total_invoice_penyedia'  => $invoiceAll->where('periode_bulan', $m)->sum('total_nilai'),
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Bulan', 'Jumlah Pengajuan', 'Total Nilai LS', 'Transfer Penyedia', 'Invoice Penyedia',
        ];
    }

    public function map($row): array
    {
        return [
            $row['bulan'],
            $row['jumlah_pengajuan'],
            $row['total_nilai_pengajuan'],
            $row['total_transfer_penyedia'],
            $row['total_invoice_penyedia'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEF9C3']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Pembayaran LS';
    }
}
