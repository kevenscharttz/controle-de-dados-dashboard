<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;

class UserCountWidget extends Widget
{
    /** @var view-string */
    protected string $view = 'filament.widgets.user-count-widget';

    protected function getViewData(): array
    {
        return [
            'count' => User::count(),
        ];
    }
}
