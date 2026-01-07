<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\EnsureCanViewDashboard;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            // Alterado para 'painel' para evitar URL duplicada /home/home
            ->id('painel')
            ->path('painel')
            ->login()
            // Expand content area to full width (user request): can rollback by removing maxContentWidth()
            ->maxContentWidth('full')
            ->colors([
                'primary' => [
                    50 => '#eef7ff',
                    100 => '#d9edff',
                    200 => '#bce0ff',
                    300 => '#8eccff',
                    400 => '#59b0ff',
                    500 => '#3b8def',
                    600 => '#2570e3',
                    700 => '#1e5bc7',
                    800 => '#1e4ba3',
                    900 => '#1e4081',
                    950 => '#162951',
                ],
            ])
            ->font('Inter')
            ->brandName('Observatório de Dados')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->pages([
                \App\Filament\Pages\HomePage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
                \App\Filament\Widgets\UserCountWidget::class,
                \App\Filament\Widgets\DashboardsCountWidget::class,
                \App\Filament\Widgets\RecentDashboardsWidget::class,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                EnsureCanViewDashboard::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
