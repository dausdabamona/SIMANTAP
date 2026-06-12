<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SenatAccount;

class SenatAccountSeeder extends Seeder
{
    public function run(): void
    {
        // Rekening BSI — untuk Tingkat I
        SenatAccount::firstOrCreate(
            ['nomor_rekening' => '7200012345678'],
            [
                'nama_akun'     => 'Rekening Senat BSI Tingkat I',
                'bank'          => 'BSI (Bank Syariah Indonesia)',
                'bank_group'    => 'BSI',
                'untuk_tingkat' => '1',
                'nama_pemilik'  => 'Senat Taruna Politeknik KP Sorong',
                'is_aktif'      => true,
                'keterangan'    => 'Rekening penampungan BSI — digunakan untuk taruna Tingkat I.',
            ]
        );

        // Rekening BNI — untuk Tingkat II & III
        SenatAccount::firstOrCreate(
            ['nomor_rekening' => '1234567890'],
            [
                'nama_akun'     => 'Rekening Senat BNI Tingkat II & III',
                'bank'          => 'BNI (Bank Negara Indonesia)',
                'bank_group'    => 'BNI',
                'untuk_tingkat' => '2,3',
                'nama_pemilik'  => 'Senat Taruna Politeknik KP Sorong',
                'is_aktif'      => true,
                'keterangan'    => 'Rekening penampungan BNI — digunakan untuk taruna Tingkat II dan III.',
            ]
        );
    }
}
