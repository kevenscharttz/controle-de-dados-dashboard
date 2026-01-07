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
        $email = env('DOCKER_ADMIN_EMAIL', 'admin@localhost');
    $password = env('DOCKER_ADMIN_PASSWORD', 'password');

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
            $role = Role::firstOrCreate(['name' => 'super-admin']);
            if (! $user->hasRole('super-admin')) {
                $user->assignRole('super-admin');
            }
        } catch (\Throwable $e) {
            // Spatie tables may not be present or package not installed; ignore gracefully
            // Logging would help, but we keep it silent for bootstrap
        }
    }
}
