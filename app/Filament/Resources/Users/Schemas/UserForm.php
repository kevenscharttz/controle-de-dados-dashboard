<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\Organization;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->string()
                    ->maxLength(100),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->string()
                    ->maxLength(120)
                    // Evita erro 500 por violar índice único na tabela `users`
                    // Exibe validação amigável e ignora o próprio registro em edições
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->string()
                    ->minLength(8)
                    ->maxLength(100)
                    // Só dehidratar (salvar) se o campo estiver preenchido; evita sobrescrever com null no edit
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),
                Select::make('role')
                    ->label('Função')
                    ->options(function () {
                        $current = Auth::user();
                        try {
                            $guard = config('auth.defaults.guard', 'web');
                            $all = Role::query()->where('guard_name', $guard)->pluck('name', 'id')->toArray();
                        } catch (\Throwable $e) {
                            Log::error('Falha ao carregar roles para UserForm: ' . $e->getMessage());
                            $all = [];
                        }
                        $isSuper = $current && method_exists($current, 'hasRole') && ($current->hasRole('super-admin') || $current->hasRole('super_admin'));
                        if ($isSuper) {
                            return $all;
                        }
                        // Para não-super, ocultar tanto 'super-admin' quanto 'super_admin'
                        return collect($all)->reject(fn($name) => in_array($name, ['super-admin','super_admin'], true))->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->afterStateUpdated(function ($state, $livewire) {
                    })
                    // Não tentar salvar coluna inexistente 'role' na tabela users
                    ->dehydrated(false)
                    ->saveRelationshipsUsing(function ($component, $record, $state) {
                        try {
                            if ($state) {
                                $guard = config('auth.defaults.guard', 'web');
                                $role = Role::query()->whereKey($state)->where('guard_name', $guard)->first();
                                if ($role) {
                                    $actor = Auth::user();
                                    $actorIsSuper = $actor && method_exists($actor, 'hasRole') && ($actor->hasRole('super-admin') || $actor->hasRole('super_admin'));
                                    if (in_array($role->name, ['super-admin','super_admin'], true) && ! $actorIsSuper) {
                                        return;
                                    }
                                    $record->syncRoles([$role->name]);
                                }
                            } else {
                                $record->syncRoles([]);
                            }
                        } catch (\Throwable $e) {
                            Log::error('Falha ao sincronizar role no UserForm: ' . $e->getMessage());
                        }
                    }),

                Select::make('organizations')
                    ->label('Organizações')
                    ->helperText('Selecione as organizações às quais o usuário pertence')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->relationship(
                        name: 'organizations',
                        titleAttribute: 'name',
                        modifyQueryUsing: function ($query) {
                            $current = Auth::user();
                            if (! $current) {
                                return $query->whereRaw('0=1');
                            }
                            if (method_exists($current, 'hasRole') && $current->hasRole('super-admin')) {
                                return $query;
                            }
                            if (method_exists($current, 'hasRole') && $current->hasRole('organization-manager')) {
                                $orgIds = $current->organizations()->pluck('organizations.id');
                                return $query->whereIn('organizations.id', $orgIds);
                            }
                            $orgIds = $current->organizations()->pluck('organizations.id');
                            return $query->whereIn('organizations.id', $orgIds);
                        }
                    )
                    ->default(function () {
                        $current = Auth::user();
                        if (! $current) {
                            return [];
                        }
                        if (method_exists($current, 'hasRole') && $current->hasRole('organization-manager')) {
                            $orgIds = $current->organizations()->pluck('organizations.id');
                            if ($orgIds->count() === 1) {
                                return [$orgIds->first()];
                            }
                        }
                        return [];
                    })
                    ->required(fn() => Auth::user()?->hasRole('organization-manager')),
            ]);
    }
}
