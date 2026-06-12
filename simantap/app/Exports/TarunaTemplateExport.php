<?php

namespace App\Exports;

use App\Models\Prodi;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TarunaTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new TarunaTemplateSheet(),
            new TarunaReferensiProdiSheet(),
            new TarunaPetunjukSheet(),
        ];
    }
}
