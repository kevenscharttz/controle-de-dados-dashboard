<?php

namespace App\Filament\Widgets;

use App\Models\Dashboard;
use Filament\Widgets\Widget;

class DashboardsCountWidget extends Widget
{
    /** @var view-string */
    protected string $view = 'filament.widgets.dashboards-count-widget';

    protected function getViewData(): array
    {
        return [
            'count' => Dashboard::count(),
        ];
    }
}
