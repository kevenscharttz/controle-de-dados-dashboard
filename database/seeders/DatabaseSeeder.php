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
        // Seeders essenciais para produção:
        // - Super Admin (idempotente)
        // Observação: desativamos o seeder de Roles & Permissions para evitar que suas alterações
        // de permissões sejam sobrescritas em cada execução de db:seed.
        // Caso queira reativar, chame PlatformRolesAndPermissionsSeeder manualmente.
        $this->call([
            DockerSuperAdminSeeder::class,
        ]);
    }
}
