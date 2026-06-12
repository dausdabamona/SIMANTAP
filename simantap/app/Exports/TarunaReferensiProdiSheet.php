<?php

namespace App\Exports;

use App\Models\Prodi;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TarunaReferensiProdiSheet implements FromQuery, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Referensi Prodi';
    }

    public function query()
    {
        return Prodi::select('kode_prodi', 'nama_prodi', 'jenjang')
            ->where('is_active', true)
            ->orderBy('kode_prodi');
    }

    public function headings(): array
    {
        return ['Kode Prodi', 'Nama Prodi', 'Jenjang'];
    }
}
