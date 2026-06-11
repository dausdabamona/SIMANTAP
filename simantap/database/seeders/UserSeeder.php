<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Super Administrator',
                'email'    => 'superadmin@poltekkpsorong.ac.id',
                'password' => Hash::make('password123'),
                'role'     => 'super_admin',
            ],
            [
                'name'     => 'Kuasa Pengguna Anggaran',
                'email'    => 'kpa@poltekkpsorong.ac.id',
                'password' => Hash::make('password123'),
                'role'     => 'kpa',
            ],
            [
                'name'     => 'Pejabat Pembuat Komitmen',
                'email'    => 'ppk@poltekkpsorong.ac.id',
                'password' => Hash::make('password123'),
                'role'     => 'ppk',
            ],
            [
                'name'     => 'Pembina Karakter',
                'email'    => 'pembina@poltekkpsorong.ac.id',
                'password' => Hash::make('password123'),
                'role'     => 'pembina_karakter',
            ],
            [
                'name'     => 'Senat Taruna',
                'email'    => 'senat@poltekkpsorong.ac.id',
                'password' => Hash::make('password123'),
                'role'     => 'senat_taruna',
            ],
            [
                'name'     => 'Auditor Internal',
                'email'    => 'auditor@poltekkpsorong.ac.id',
                'password' => Hash::make('password123'),
                'role'     => 'auditor',
            ],
            [
                'name'     => 'Viewer',
                'email'    => 'viewer@poltekkpsorong.ac.id',
                'password' => Hash::make('password123'),
                'role'     => 'viewer',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $user->syncRoles([$role]);
        }
    }
}
