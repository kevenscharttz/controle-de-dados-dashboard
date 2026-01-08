<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DockerSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates a super-admin user (idempotent).
     *
     * @return void
     */
    public function run(): void
    {
        // Prefer DOCKER_* vars for container environments; fallback to ADMIN_* if not set
        $email = env('DOCKER_ADMIN_EMAIL') ?? env('ADMIN_EMAIL', 'admin@localhost');
        $password = env('DOCKER_ADMIN_PASSWORD') ?? env('ADMIN_PASSWORD', 'password');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => 'Super Admin',
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        }

        // Ensure role exists and assign it
        try {
            $role = Role::firstOrCreate(['name' => 'super_admin']);
            if (! $user->hasRole('super_admin')) {
                $user->assignRole('super_admin');
            }
        } catch (\Throwable $e) {
            // Spatie tables may not be present or package not installed; ignore gracefully
            // Logging would help, but we keep it silent for bootstrap
        }
    }
}
