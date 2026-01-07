<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Report;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportAuthorizationTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        // For tests use in-memory sqlite to avoid needing mysql driver
    putenv('DB_CONNECTION=sqlite');
    $_ENV['DB_CONNECTION'] = 'sqlite';
    $_SERVER['DB_CONNECTION'] = 'sqlite';
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', database_path('testing.sqlite'));
        if (! file_exists(database_path('testing.sqlite'))) {
            touch(database_path('testing.sqlite'));
        }
        // Run migrations fresh to ensure schema
        $this->artisan('migrate:fresh');
        // Seed base permissions/roles so policies reflect real state
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\PlatformRolesAndPermissionsSeeder']);
    }

    public function test_regular_user_cannot_access_report_create_page(): void
    {
        $user = User::factory()->create();
        // Ensure 'user' role exists & assign
        Role::firstOrCreate(['name' => 'user']);
        $user->assignRole('user');
        $this->actingAs($user);
        // Gate check only; Filament route may add extra resource-level permissions
        $this->assertFalse($user->can('create', Report::class));
    }

    public function test_manager_can_access_report_create_page(): void
    {
        $manager = User::factory()->create();
        Role::firstOrCreate(['name' => 'organization-manager']);
        $manager->assignRole('organization-manager');

        $this->actingAs($manager);
        $this->assertTrue($manager->can('create', Report::class));
    }
}
