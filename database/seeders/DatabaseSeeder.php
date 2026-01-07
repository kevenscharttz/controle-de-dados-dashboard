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
        // User::factory(10)->create();


        $adminName = env('ADMIN_NAME', 'Admin User');
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $adminPassword = env('ADMIN_PASSWORD', 'password');
        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => bcrypt($adminPassword),
            ]
        );
        $this->command->info("Usuário admin criado/atualizado: $adminEmail / $adminPassword");

        // Seed platform roles and permissions (spatie)
        // Ordem ajustada para evitar que seeds de teste removam dados de organizações criadas previamente
        $this->call([
            PlatformRolesAndPermissionsSeeder::class,
            OrganizationDataSeeder::class,
            TestDataSeeder::class, // agora não trunca organizations
            AddLogosToOrganizationsSeeder::class,
            DockerSuperAdminSeeder::class,
        ]);
    }
}
