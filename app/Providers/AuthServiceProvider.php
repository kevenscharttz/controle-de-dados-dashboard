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
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Optional: a 'before' gate for super-admins
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasRole') && ($user->hasRole('super-admin') || $user->hasRole('super_admin'))) {
                return true;
            }
        });
    }
}
