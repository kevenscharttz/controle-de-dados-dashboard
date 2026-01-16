<?php

namespace App\Filament\Clusters\ObservatorioDeDados\Pages;

use App\Filament\Clusters\ObservatorioDeDados;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Dashboard;

class TodosDashboards extends Page
{
    protected static ?string $navigationLabel = 'Todos os Dashboards';
    protected static ?string $title = 'Observatório de Dados';
    protected static ?string $cluster = ObservatorioDeDados::class;
    protected static ?string $slug = 'todos';

    protected string $view = 'filament.clusters.observatorio-de-dados.todos';

    public function getHeading(): string
    {
        return 'Observatório de Dados';
    }

    public function getSubheading(): ?string
    {
        return 'Coleção de dashboards visíveis para você';
    }

    protected function getViewData(): array
    {
        $user = Auth::user();
        $dashboards = Dashboard::query()->visibleTo($user)->orderBy('id')->get();

        return [
            'dashboards' => $dashboards,
        ];
    }
}
