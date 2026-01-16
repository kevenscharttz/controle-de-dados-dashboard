<?php

namespace App\Filament\Clusters\ObservatorioDeDados\Pages;

use App\Filament\Clusters\ObservatorioDeDados;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Dashboard;

class ListaDashboards extends Page
{
    protected static ?string $navigationLabel = 'Lista';
    protected static ?string $title = 'Dashboards';
    protected static ?string $cluster = ObservatorioDeDados::class;
    protected static ?string $slug = 'lista';

    protected string $view = 'filament.clusters.observatorio-de-dados.lista';

    protected function getViewData(): array
    {
        $user = Auth::user();
        return [
            'dashboards' => Dashboard::query()->visibleTo($user)->orderBy('id')->get(),
        ];
    }
}
