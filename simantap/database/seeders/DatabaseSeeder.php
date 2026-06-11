<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Order matters: roles/permissions must exist before users are assigned roles.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            SenatAccountSeeder::class,
            PaguAnggaranSeeder::class,
        ]);
    }
}
