<?php

namespace App\Filament\Resources\Dashboards\Pages;

use Filament\Resources\Pages\Page;
use App\Filament\Resources\Dashboards\DashboardResource;
use Illuminate\Support\Facades\Auth;
use App\Models\DashboardFolder;
use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ManageFolders extends Page
{
    protected static string $resource = DashboardResource::class;
    protected static ?string $title = 'Dashboards';
    protected static ?string $navigationLabel = 'Dashboards';
    protected string $view = 'filament.resources.dashboards.manage-folders';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_folder')
                ->label('Criar Pasta')
                ->icon('heroicon-o-folder-plus')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->label('Nome da Pasta')
                        ->required()
                        ->maxLength(100),
                    \Filament\Forms\Components\Select::make('organization_id')
                        ->label('Organização')
                        ->options(function () {
                            $user = Auth::user();
                            if (! $user) return [];
                            if (method_exists($user, 'hasRole') && ($user->hasRole('super_admin') || $user->hasRole('super-admin'))) {
                                return Organization::pluck('name', 'id');
                            }
                            $orgIds = $user->organizations()->pluck('id');
                            return Organization::whereIn('id', $orgIds)->pluck('name', 'id');
                        })
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data): void {
                    $user = Auth::user();
                    DashboardFolder::create([
                        'name' => $data['name'],
                        'organization_id' => $data['organization_id'],
                        'created_by' => $user?->id,
                    ]);
                    Notification::make()->title('Pasta criada')->success()->send();
                }),
        ];
    }

    public function deleteFolder(int $id): void
    {
        $folder = DashboardFolder::find($id);
        if ($folder) {
            $folder->delete();
            Notification::make()->title('Pasta excluída')->success()->send();
        }
    }

    public function renameFolder(int $id, string $name): void
    {
        $folder = DashboardFolder::find($id);
        if ($folder) {
            $folder->update(['name' => $name]);
            Notification::make()->title('Pasta renomeada')->success()->send();
        }
    }

    public function getViewData(): array
    {
        $user = Auth::user();
        $orgIds = $user?->organizations()->pluck('organizations.id') ?? collect();
        $folders = DashboardFolder::query()
            ->whereIn('organization_id', $orgIds)
            ->orderBy('name')
            ->get();

        return [
            'folders' => $folders,
        ];
    }
}
