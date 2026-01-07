<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        // Criar as roles (se não existirem)
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $organizationManager = Role::firstOrCreate(['name' => 'organization-manager']);
        $user = Role::firstOrCreate(['name' => 'user']);

        // Permissões Organization Manager
        $organizationManagerPermissions = [
            'ViewAny:Organization',
            'View:Organization',
            'Create:Organization',
            'Update:Organization',
            'Delete:Organization',
            'ForceDelete:Organization',
            'ViewAny:Dashboard',
            'View:Dashboard',
            'Create:Dashboard',
            'Update:Dashboard',
            'Delete:Dashboard'
        ];

        // Permissões User Normal
        $userPermissions = [
            'View:Organization',
            'View:Dashboard',
            'Update:Profile'
        ];

        // Permissões Super Admin (inclui todas as permissões)
        $superAdminPermissions = array_merge(
            $organizationManagerPermissions,
            $userPermissions,
            [
                'ViewAny:Role',
                'View:Role',
                'Create:Role',
                'Update:Role',
                'Delete:Role',
                'ForceDelete:Role'
            ]
        );

        // Criar permissões (se não existirem)
        foreach ($superAdminPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Limpar permissões existentes
        $superAdmin->syncPermissions([]);
        $organizationManager->syncPermissions([]);
        $user->syncPermissions([]);

        // Atribuir permissões às roles
        $superAdmin->givePermissionTo($superAdminPermissions);
        $organizationManager->givePermissionTo($organizationManagerPermissions);
        $user->givePermissionTo($userPermissions);
    }
}