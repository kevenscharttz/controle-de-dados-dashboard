<?php

namespace App\Filament\Widgets;

use App\Models\Dashboard;
use Filament\Widgets\Widget;

class RecentDashboardsWidget extends Widget
{
    /** @var view-string */
    protected string $view = 'filament.widgets.recent-dashboards-widget';

    protected function getViewData(): array
    {
        return [
            'dashboards' => Dashboard::latest()->limit(5)->get(),
        ];
    }
}
