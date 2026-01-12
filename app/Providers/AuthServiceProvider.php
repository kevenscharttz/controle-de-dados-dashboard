<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Dashboard;
use App\Models\Report;
use App\Models\User;
use App\Policies\DashboardPolicy;
use App\Policies\ReportPolicy;
use App\Policies\UserPolicy;
use App\Policies\RolePolicy;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Dashboard::class => DashboardPolicy::class,
        Report::class => ReportPolicy::class,
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // 'before' gate: allow super-admins everything, and hide Role management from common users
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasRole')) {
                // Super admins: full access
                if ($user->hasRole('super-admin') || $user->hasRole('super_admin')) {
                    return true;
                }

                // Common users should not see/manage Roles
                // Filament Shield uses permission names like "ViewAny:Role", "View:Role", "Create:Role", etc.
                $isRoleAbility = is_string($ability) && str_contains($ability, ':Role');
                if ($isRoleAbility) {
                    $isCommon = $user->hasRole('user') || $user->hasRole('usuario') || $user->hasRole(config('filament-shield.panel_user.name', 'panel_user'));
                    $isElevated = $user->hasRole('organization-manager');
                    if ($isCommon && ! $isElevated) {
                        return false; // explicitly deny role-related permissions
                    }
                }
            }
            return null; // fall through to normal policy/permissions
        });
    }
}
