<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class OrganizationDataSeeder extends Seeder
{
    public function run(): void
    {
        // Criar roles se não existirem
        $roles = [
            'super-admin',
            'organization-manager',
            'user'
        ];

        foreach ($roles as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                Role::create(['name' => $roleName]);
            }
        }

        // Criar usuário super-admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super-admin');

        // Criar 3 organization-managers
        $managers = [];
        for ($i = 1; $i <= 3; $i++) {
            $manager = User::create([
                'name' => "Manager {$i}",
                'email' => "manager{$i}@example.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $manager->assignRole('organization-manager');
            $managers[] = $manager;
        }

        // Cada manager cria uma organização
        foreach ($managers as $index => $manager) {
            $organization = Organization::create([
                'name' => "Organização {$manager->name}",
                'slug' => \Illuminate\Support\Str::slug("organizacao-{$manager->name}"),
                'description' => "Organização criada pelo {$manager->name}",
                'single_dashboard' => ($index % 2 == 0), // Alterna entre true/false
                'created_by' => $manager->id,
            ]);

            // Associar o manager à sua organização
            $organization->users()->attach($manager->id);

            // Criar 3 usuários para esta organização
            for ($userIndex = 1; $userIndex <= 3; $userIndex++) {
                $user = User::create([
                    'name' => "Usuário {$userIndex} - {$organization->name}",
                    'email' => "usuario{$userIndex}.org" . ($index + 1) . "@example.com",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]);

                // Associar usuário à organização
                $organization->users()->attach($user->id);

                // Atribuir role de usuário comum
                $user->assignRole('user');
            }
        }

        $this->command->info('Dados de organização criados com sucesso!');
        $this->command->info('Super Admin: admin@admin.com / password');
        $this->command->info('Managers: manager1@example.com, manager2@example.com, manager3@example.com / password');
        $this->command->info('Total de organizações: ' . Organization::count());
        $this->command->info('Total de usuários: ' . User::count());
        $this->command->info('Managers: ' . User::role('organization-manager')->count());
        $this->command->info('Usuários comuns: ' . User::role('user')->count());
    }
}