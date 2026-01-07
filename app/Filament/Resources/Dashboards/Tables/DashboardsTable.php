<?php

namespace App\Filament\Resources\Dashboards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DashboardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                    TextColumn::make('title')
                        ->label('Título')
                        ->searchable()
                        ->sortable()
                        ->weight('bold')
                        ->description(fn ($record) => $record->description ?? 'Sem descrição')
                        ->wrap(),

                    TextColumn::make('type')
                        ->label('Tipo')
                        ->searchable()
                        ->sortable()
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'dashboard' => 'primary',
                            'report' => 'success',
                        })
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'dashboard' => 'Dashboard',
                            'report' => 'Relatório',
                        }),

                TextColumn::make('platform')
                    ->label('Plataforma')
                    ->searchable()
                    ->sortable()
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

                TextColumn::make('visibility')
                    ->label('Visibilidade')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'public' => 'success',
                        'private' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'public' => 'Público',
                        'private' => 'Privado',
                    }),

                TextColumn::make('tags')
                    ->label('Tags')
                    ->searchable()
                    ->badge()
                    ->separator(',')
                    ->color('gray')
                    ->limit(3)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (is_array($state) && count($state) > 3) {
                            return implode(', ', $state);
                        }
                        return null;
                    }),

                TextColumn::make('url')
                    ->label('URL')
                    ->formatStateUsing(fn ($record) => !empty($record->url) ? '✓' : '✗')
                    ->color(fn ($record) => !empty($record->url) ? 'success' : 'danger'),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('platform')
                    ->label('Plataforma')
                    ->options([
                        'Power BI' => 'Power BI',
                        'Metabase' => 'Metabase',
                        'Tableau' => 'Tableau',
                        'Grafana' => 'Grafana',
                        'Looker' => 'Looker',
                        'QlikView' => 'QlikView',
                        'Outros' => 'Outros',
                    ])
                    ->searchable(),

                TernaryFilter::make('visibility')
                    ->label('Visibilidade')
                    ->placeholder('Todos')
                    ->trueLabel('Público')
                    ->falseLabel('Privado')
                    ->queries(
                        true: fn ($query) => $query->where('visibility', 'public'),
                        false: fn ($query) => $query->where('visibility', 'private'),
                    ),

                TernaryFilter::make('has_url')
                    ->label('Com URL')
                    ->placeholder('Todos')
                    ->trueLabel('Com URL')
                    ->falseLabel('Sem URL')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('url')->where('url', '!=', ''),
                        false: fn ($query) => $query->where(function ($q) {
                            $q->whereNull('url')->orWhere('url', '');
                        }),
                    ),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Visualizar')
                    ->icon('heroicon-o-eye')
                    ->color('info'),
                

                EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->color('warning'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Excluir Selecionados')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
