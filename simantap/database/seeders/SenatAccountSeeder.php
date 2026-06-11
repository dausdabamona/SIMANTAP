<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SenatAccount;

class SenatAccountSeeder extends Seeder
{
    public function run(): void
    {
        SenatAccount::firstOrCreate(
            ['nomor_rekening' => '1234567890'],
            [
                'nama_akun'    => 'Rekening Senat Taruna Poltek KP Sorong',
                'bank'         => 'Bank BRI',
                'nama_pemilik' => 'Senat Taruna Politeknik KP Sorong',
                'is_aktif'     => true,
                'keterangan'   => 'Rekening penampungan dana bantuan makan taruna sebelum ditransfer ke penyedia makan.',
            ]
        );
    }
}
