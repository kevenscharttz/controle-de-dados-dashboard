<?php

namespace App\Filament\Resources\Organizations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class OrganizationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nome da Organização')
                    ->weight('bold')
                    ->size('xl')
                    ->color('primary')
                    ->columnSpanFull(),

                ImageEntry::make('logo_url')
                    ->label('Logo')
                    ->getStateUsing(function ($record) {
                        $state = $record->logo_url;
                        return ($state && Storage::disk('public')->exists($state)) ? $state : null;
                    })
                    ->disk('public')
                    ->height('100px')
                    ->width('200px')
                    ->defaultImageUrl(asset('images/default-organization-logo.svg'))
                    ->columnSpanFull(),

                TextEntry::make('slug')
                    ->label('Identificador')
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->copyMessage('Slug copiado!'),

                TextEntry::make('single_dashboard')
                    ->label('Configuração de Dashboard')
                    ->formatStateUsing(fn ($state) => $state ? 'Apenas um dashboard' : 'Múltiplos dashboards')
                    ->badge()
                    ->color(fn ($state) => $state ? 'warning' : 'success'),

                TextEntry::make('description')
                    ->label('Descrição')
                    ->placeholder('Nenhuma descrição fornecida')
                    ->columnSpanFull()
                    ->markdown()
                    ->prose(),

                TextEntry::make('users_count')
                    ->label('Total de Usuários')
                    ->formatStateUsing(fn ($record) => $record->users()->count())
                    ->badge()
                    ->color('blue')
                    ->icon('heroicon-o-users'),

                TextEntry::make('dashboards_count')
                    ->label('Total de Dashboards')
                    ->formatStateUsing(fn ($record) => $record->dashboards()->count())
                    ->badge()
                    ->color('green')
                    ->icon('heroicon-o-chart-bar-square'),

                TextEntry::make('reports_count')
                    ->label('Total de Relatórios')
                    ->formatStateUsing(fn ($record) => $record->reports()->count())
                    ->badge()
                    ->color('purple')
                    ->icon('heroicon-o-document-text'),

                TextEntry::make('creator.name')
                    ->label('Criado por')
                    ->placeholder('Sistema')
                    ->badge()
                    ->color('green')
                    ->icon('heroicon-o-user'),

                TextEntry::make('created_at')
                    ->label('Data de Criação')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->icon('heroicon-o-calendar'),

                TextEntry::make('updated_at')
                    ->label('Última Atualização')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->icon('heroicon-o-clock'),
            ]);
    }
}
