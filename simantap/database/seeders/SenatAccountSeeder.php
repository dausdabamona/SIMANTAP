<?php

namespace Database\Seeders;

use App\Models\SenatAccount;
use Illuminate\Database\Seeder;

class SenatAccountSeeder extends Seeder
{
    public function run(): void
    {
        SenatAccount::firstOrCreate(
            ['nomor_rekening' => '1234567890'],
            [
                'nama'         => 'Rekening Penampungan Senat Taruna Poltek KP Sorong',
                'bank'         => 'Bank BRI',
                'nama_pemilik' => 'Senat Taruna Politeknik KP Sorong',
                'is_active'    => true,
                'keterangan'   => 'Rekening penampungan untuk mekanisme debit otomatis bank (SOP PR/PKU/KU-001/2025 langkah 11 & 14)',
            ]
        );
    }
}
