<?php

namespace App\Filament\Resources\Dashboards\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class DashboardInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Título')
                    ->weight('bold')
                    ->size('lg'),
                
                TextEntry::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'dashboard' => 'primary',
                        'report' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'dashboard' => 'Dashboard',
                        'report' => 'Relatório',
                    }),
                
                TextEntry::make('organization.name')
                    ->label('Organização')
                    ->badge()
                    ->color('blue'),

                TextEntry::make('platform')
                    ->label('Plataforma')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Power BI' => 'primary',
                        'Metabase' => 'success',
                        'Tableau' => 'warning',
                        'Grafana' => 'info',
                        'Looker' => 'secondary',
                        'QlikView' => 'gray',
                        default => 'danger',
                    }),

                TextEntry::make('visibility')
                    ->label('Visibilidade')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'public' => 'success',
                        'private' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'public' => 'Público',
                        'private' => 'Privado',
                    }),

                TextEntry::make('description')
                    ->label('Descrição')
                    ->placeholder('Sem descrição')
                    ->columnSpanFull()
                    ->markdown(),

                TextEntry::make('tags')
                    ->label('Tags')
                    ->placeholder('Nenhuma tag')
                    ->badge()
                    ->separator(',')
                    ->color('gray')
                    ->columnSpanFull(),

                View::make('filament.dashboards.preview')
                    ->columnSpanFull()
                    ->visible(fn ($record) => filled($record?->url)),

                TextEntry::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
            ]);
    }
}
