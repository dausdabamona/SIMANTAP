<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaguAnggaran;

class PaguAnggaranSeeder extends Seeder
{
    public function run(): void
    {
        $paguList = [
            [
                'tahun'        => 2025,
                'akun_belanja' => '524111',
                'nilai_pagu'   => 1_500_000_000.00,
                'keterangan'   => 'Belanja Perjalanan Dinas Biasa - Bantuan Makan Taruna TA 2025',
            ],
            [
                'tahun'        => 2025,
                'akun_belanja' => '524113',
                'nilai_pagu'   => 200_000_000.00,
                'keterangan'   => 'Belanja Perjalanan Dinas Dalam Kota - Monev TA 2025',
            ],
            [
                'tahun'        => 2026,
                'akun_belanja' => '524111',
                'nilai_pagu'   => 1_650_000_000.00,
                'keterangan'   => 'Belanja Perjalanan Dinas Biasa - Bantuan Makan Taruna TA 2026',
            ],
        ];

        foreach ($paguList as $pagu) {
            PaguAnggaran::firstOrCreate(
                [
                    'tahun'        => $pagu['tahun'],
                    'akun_belanja' => $pagu['akun_belanja'],
                ],
                [
                    'nilai_pagu'  => $pagu['nilai_pagu'],
                    'keterangan'  => $pagu['keterangan'],
                ]
            );
        }
    }
}
