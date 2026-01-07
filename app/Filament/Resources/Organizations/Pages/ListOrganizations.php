<?php

namespace App\Filament\Resources\Organizations\Pages;

use App\Filament\Resources\Organizations\OrganizationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use App\Models\Organization;

class ListOrganizations extends ListRecords
{
    protected static string $resource = OrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = Auth::user();
        if (! $user) {
            return Organization::query()->whereRaw('0=1');
        }
        // Super-admin vê todas as organizações
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return Organization::query()
                ->with(['creator'])
                ->withCount(['users', 'dashboards', 'reports']);
        }

        // Restringe a listagem às organizações do usuário + eager loading & counts
        $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
        return Organization::query()
            ->whereIn('id', $orgIds)
            ->with(['creator'])
            ->withCount(['users', 'dashboards', 'reports']);
    }

    protected function getTableExtraAttributes(): array
    {
        return [
            'style' => 'width: 100%; max-width: none;',
        ];
    }
}
