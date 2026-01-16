<?php

namespace App\Filament\Resources\Dashboards\Pages;

use App\Filament\Resources\Dashboards\DashboardResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use App\Models\Dashboard;
use Illuminate\Support\Facades\Auth;
use App\Models\Organization;
use Filament\Notifications\Notification;
use App\Models\DashboardFolder;
// ...existing code...

class ListDashboards extends ListRecords
{
    protected static string $resource = DashboardResource::class;

    protected function getHeaderActions(): array
    {
        $folderId = request()->route('folder') ?? request()->query('folder_id');
        $folder = $folderId ? DashboardFolder::find($folderId) : null;

        return [
            CreateAction::make()
                ->label('Criar Dashboard')
                ->url(function () use ($folderId, $folder) {
                    $params = [];
                    if ($folderId) {
                        $params['folder_id'] = $folderId;
                        if ($folder) {
                            $params['organization_id'] = $folder->organization_id;
                        }
                    }
                    return static::getResource()::getUrl('create', $params);
                }),
            // No folder management actions here; this page is strictly for dashboards inside a folder.
        ];
    }

    // Use Filament's default table listing (no custom content override)

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = Auth::user();
        $query = Dashboard::query()->visibleTo($user);

    $folderId = request()->route('folder') ?? request()->query('folder_id');
        if ($folderId) {
            $query->where('folder_id', $folderId);
        }

        return $query;
    }

    protected function getTableExtraAttributes(): array
    {
        return [
            'style' => 'width: 100%; max-width: none;',
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $user = Auth::user();
        if (! $user) {
            return;
        }

        // If user belongs to exactly one organization and it is single_dashboard, redirect behavior
        $org = $user->organizations()->first();
        if ($org && $user->organizations()->count() === 1 && (bool) $org->single_dashboard) {
            $existing = Dashboard::where('organization_id', $org->id)->first();
            if ($existing) {
                $this->redirect(static::getResource()::getUrl('view', ['record' => $existing]));
                return;
            }

            // Redirect to create with preselected organization
            $this->redirect(static::getResource()::getUrl('create', ['organization_id' => $org->id]));
            return;
        }
    }
}
