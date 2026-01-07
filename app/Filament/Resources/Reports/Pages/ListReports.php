<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Models\Report;
use App\Models\User;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\Auth;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getTableExtraAttributes(): array
    {
        return [
            'style' => 'width: 100%; max-width: none;',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo Relatório')
                ->visible(fn () => \App\Filament\Resources\Reports\ReportResource::canCreate()),
        ];
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = Auth::user();
        return Report::query()->visibleTo($user);
    }
}
