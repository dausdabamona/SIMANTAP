<?php

namespace Database\Seeders;

use App\Models\PaguAnggaran;
use Illuminate\Database\Seeder;

class PaguAnggaranSeeder extends Seeder
{
    public function run(): void
    {
        PaguAnggaran::firstOrCreate(
            ['tahun' => 2025, 'akun_belanja' => '524111'],
            [
                'uraian_akun'      => 'Belanja Perjalanan Dinas Biasa (Bantuan Uang Makan Taruna)',
                'nilai_pagu'       => 1_500_000_000.00,
                'nilai_realisasi'  => 0,
                'nomor_dipa'       => 'SP DIPA-032.04.2.405001/2025',
                'tanggal_dipa'     => '2024-11-30',
                'keterangan'       => 'Pagu DIPA TA 2025 Politeknik KP Sorong',
            ]
        );
    }
}
