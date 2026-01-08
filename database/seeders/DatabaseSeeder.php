<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Apenas os seeders essenciais para produção:
        // - Roles & Permissions
        // - Super Admin (idempotente)
        $this->call([
            PlatformRolesAndPermissionsSeeder::class,
            DockerSuperAdminSeeder::class,
        ]);
    }
}
