<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public static function getEloquentQuery()
    {
        $query = parent::getEloquentQuery();
        $user = \Filament\Facades\Filament::auth()?->user();
        if ($user instanceof User) {
            // Normalize role name checks: accept both 'super-admin' and 'super_admin'
            $isSuper = $user->hasRole('super-admin') || $user->hasRole('super_admin');

            if (! $isSuper) {
                // organization-managers and regular users should only see users in their organizations
                $orgIds = $user->organizations()->pluck('id');
                $query = $query->whereHas('organizations', function ($q) use ($orgIds) {
                    $q->whereIn('id', $orgIds);
                });
            }
        }
        return $query;
    }

    protected function getTableExtraAttributes(): array
    {
        return [
            'style' => 'width: 100%; max-width: none;',
        ];
    }
}
// ...nenhuma linha duplicada ou antiga...
