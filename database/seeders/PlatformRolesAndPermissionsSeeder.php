<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PlatformRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Define permissions
        $permissions = [
            // Dashboards
            'ViewAny:Dashboard', 'View:Dashboard', 'Create:Dashboard', 'Update:Dashboard', 'Delete:Dashboard', 'Restore:Dashboard', 'ForceDelete:Dashboard', 'ForceDeleteAny:Dashboard', 'RestoreAny:Dashboard', 'Replicate:Dashboard', 'Reorder:Dashboard',
            // Organizations
            'ViewAny:Organization', 'View:Organization', 'Create:Organization', 'Update:Organization', 'Delete:Organization', 'Restore:Organization', 'ForceDelete:Organization', 'ForceDeleteAny:Organization', 'RestoreAny:Organization', 'Replicate:Organization', 'Reorder:Organization',
            // Users
            'ViewAny:User', 'View:User', 'Create:User', 'Update:User', 'Delete:User', 'Restore:User', 'ForceDelete:User', 'ForceDeleteAny:User', 'RestoreAny:User', 'Replicate:User', 'Reorder:User',
            // Roles (Spatie)
            'ViewAny:Role', 'View:Role', 'Create:Role', 'Update:Role', 'Delete:Role', 'Restore:Role', 'ForceDelete:Role', 'ForceDeleteAny:Role', 'RestoreAny:Role', 'Replicate:Role', 'Reorder:Role',
            // Reports
            'ViewAny:Report', 'View:Report', 'Create:Report', 'Update:Report', 'Delete:Report', 'Restore:Report', 'ForceDelete:Report', 'ForceDeleteAny:Report', 'RestoreAny:Report', 'Replicate:Report', 'Reorder:Report',
            // Extra
            'Dashboard:Embed', 'Dashboard:Publish', 'User:Invite', 'User:ResetPassword', 'Organization:AssignUser', 'Settings:Manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Create roles
        $allPermissions = Permission::all();

        // Super admin -> all permissions
        $super = Role::firstOrCreate(['name' => 'super-admin']);
        $super->syncPermissions($allPermissions);

        // Organization manager -> org + dashboard + report + user management within org
        $orgPermNames = array_filter(
            $permissions,
            fn($p) => str_contains($p, ':Organization') || str_contains($p, ':Dashboard') || str_contains($p, ':Report') || str_contains($p, ':User')
        );
        $org = Role::firstOrCreate(['name' => 'organization-manager']);
        $org->syncPermissions(Permission::whereIn('name', $orgPermNames)->get());

    // (profile-manager removed) profile/role management can be handled via 'organization-manager' or 'super-admin'

        // End user -> viewing rights
        // Note: regular 'user' should not get global Dashboard view permissions
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userPermNames = array_filter($permissions, function ($p) {
            // include only View/ViewAny but exclude any Dashboard-related permissions
            if (! (str_starts_with($p, 'View:') || str_starts_with($p, 'ViewAny:'))) {
                return false;
            }
            return ! str_contains($p, ':Dashboard');
        });
        $userRole->syncPermissions(Permission::whereIn('name', $userPermNames)->get());

        // Assign super-admin role to an existing user (prefer id=1)
        $user = User::find(1) ?: User::first();
        if ($user) {
            $user->assignRole($super);
            $this->command->info(sprintf('Assigned role super-admin to user id=%d email=%s', $user->id, $user->email));
        } else {
            $this->command->warn('No users found to assign super-admin role to.');
        }

        $this->command->info('Platform roles and permissions seeded.');
    }
}
