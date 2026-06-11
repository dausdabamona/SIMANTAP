<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Super Administrator',
                'email'    => 'superadmin@simantap.id',
                'nip'      => '000000000000000001',
                'jabatan'  => 'Super Administrator',
                'role'     => 'super_admin',
            ],
            [
                'name'     => 'Kepala Satker (KPA)',
                'email'    => 'kpa@simantap.id',
                'nip'      => '197001011990031001',
                'jabatan'  => 'Kepala Politeknik KP Sorong (KPA)',
                'role'     => 'kpa',
            ],
            [
                'name'     => 'Pejabat Pembuat Komitmen',
                'email'    => 'ppk@simantap.id',
                'nip'      => '197502151999031002',
                'jabatan'  => 'PPK Kegiatan Pengembangan SDM',
                'role'     => 'ppk',
            ],
            [
                'name'     => 'Pembina Karakter',
                'email'    => 'pembina@simantap.id',
                'nip'      => '198003202005011003',
                'jabatan'  => 'Pembina Karakter Taruna',
                'role'     => 'pembina_karakter',
            ],
            [
                'name'     => 'Ketua Senat Taruna',
                'email'    => 'senat@simantap.id',
                'nip'      => 'ST-2022-001',
                'jabatan'  => 'Ketua Senat Taruna',
                'role'     => 'senat_taruna',
            ],
            [
                'name'     => 'Tim Auditor',
                'email'    => 'auditor@simantap.id',
                'nip'      => '198506102010121004',
                'jabatan'  => 'Auditor Internal',
                'role'     => 'auditor',
            ],
            [
                'name'     => 'Viewer',
                'email'    => 'viewer@simantap.id',
                'nip'      => '000000000000000002',
                'jabatan'  => 'Pengguna Terbatas',
                'role'     => 'viewer',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'nip'               => $data['nip'],
                    'jabatan'           => $data['jabatan'],
                    'password'          => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'is_active'         => true,
                ]
            );
            $user->assignRole($data['role']);
        }
    }
}
