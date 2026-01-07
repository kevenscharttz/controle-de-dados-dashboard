<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class HomePage extends Page
{
    protected static ?string $title = 'Início';
    protected static ?string $navigationLabel = 'Início';
    protected static ?string $slug = 'home';
    protected string $view = 'filament.pages.home';
    protected static ?int $navigationSort = 0;

    public function getViewData(): array
    {
        $now = now();
        $lastMonth = $now->copy()->subMonth();
        $dashboardsCount = \App\Models\Dashboard::count();
        $dashboardsLastMonth = \App\Models\Dashboard::where('created_at', '<', $lastMonth)->count();
        $dashboardsPercent = $dashboardsLastMonth ? round((($dashboardsCount - $dashboardsLastMonth) / $dashboardsLastMonth) * 100, 1) : 0;
        $usersCount = \App\Models\User::count();
        $usersLastMonth = \App\Models\User::where('created_at', '<', $lastMonth)->count();
        $usersPercent = $usersLastMonth ? round((($usersCount - $usersLastMonth) / $usersLastMonth) * 100, 1) : 0;
        $organizationsCount = \App\Models\Organization::count();
        $organizationsLastMonth = \App\Models\Organization::where('created_at', '<', $lastMonth)->count();
        $organizationsPercent = $organizationsLastMonth ? round((($organizationsCount - $organizationsLastMonth) / $organizationsLastMonth) * 100, 1) : 0;
        $recentDashboards = \App\Models\Dashboard::latest()->limit(3)->get();
        $reportsCount = \App\Models\Report::count();
        $recentReports = \App\Models\Report::latest()->limit(3)->get();
    $user = auth()->user();
    $canCreateDashboard = $user?->can('create', \App\Models\Dashboard::class) ?? false;
    $canCreateReport = \App\Filament\Resources\Reports\ReportResource::canCreate();
    $canManageUsers = $user?->can('viewAny', \App\Models\User::class) ?? false;
    $canCreateOrganization = $user?->can('create', \App\Models\Organization::class) ?? false;
        return [
            'dashboardsCount' => $dashboardsCount,
            'dashboardsPercent' => $dashboardsPercent,
            'usersCount' => $usersCount,
            'usersPercent' => $usersPercent,
            'organizationsCount' => $organizationsCount,
            'organizationsPercent' => $organizationsPercent,
            'recentDashboards' => $recentDashboards,
            'reportsCount' => $reportsCount,
            'recentReports' => $recentReports,
            'canCreateDashboard' => $canCreateDashboard,
            'canCreateReport' => $canCreateReport,
            'canManageUsers' => $canManageUsers,
            'canCreateOrganization' => $canCreateOrganization,
        ];
    }
}
