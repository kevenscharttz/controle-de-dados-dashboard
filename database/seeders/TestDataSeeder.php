<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Limpar apenas usuários e vínculos de teste anteriores (não apagar organizações existentes)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $testUserIds = User::where('email', 'like', '%@test.com')->pluck('id');
        if ($testUserIds->isNotEmpty()) {
            // Remover vínculos antigos destes usuários
            DB::table('organization_user')->whereIn('user_id', $testUserIds)->delete();
            User::whereIn('id', $testUserIds)->delete();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // Criar 3 Organization Managers com suas organizações
        for ($i = 1; $i <= 3; $i++) {
            // Criar Organization Manager
            $manager = User::create([
                'name' => "Manager {$i}",
                'email' => "manager{$i}@test.com",
                'password' => Hash::make('password'),
            ]);
            $manager->assignRole('organization-manager');

            // Criar a organização deste manager
            $organization = Organization::create([
                'name' => "Organization Test {$i}",
                'description' => "Test organization number {$i}",
                'created_by' => $manager->id,
                'slug' => "org-test-{$i}-" . strtolower(str_replace(' ', '-', uniqid())),
            ]);

            // Vincular o manager à organização
            $manager->organizations()->attach($organization->id);

            // Criar um usuário comum para esta organização
            $user = User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@test.com",
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('user');

            // Vincular o usuário à organização
            $user->organizations()->attach($organization->id);
        }

        // Log das credenciais criadas
        $this->command->info('Test accounts created:');
        $this->command->info('Organization Managers:');
        for ($i = 1; $i <= 3; $i++) {
            $this->command->info("manager{$i}@test.com / password");
        }
        $this->command->info('Regular Users:');
        for ($i = 1; $i <= 3; $i++) {
            $this->command->info("user{$i}@test.com / password");
        }
    }
}