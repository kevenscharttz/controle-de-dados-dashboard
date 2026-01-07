<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Collection;
use Illuminate\Auth\Access\AuthorizationException;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label('E-mail verificado em')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn ($record) => \Illuminate\Support\Facades\Gate::allows('delete', $record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            $user = auth()->user();
                            foreach ($records as $record) {
                                if (!\Illuminate\Support\Facades\Gate::forUser($user)->allows('delete', $record)) {
                                    throw new AuthorizationException('Você não tem permissão para excluir um ou mais registros selecionados.');
                                }
                            }

                            // If all authorized, perform delete
                            $records->each->delete();
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}
