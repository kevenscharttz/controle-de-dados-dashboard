<?php

namespace App\Filament\Resources\Dashboards\Pages;

use App\Filament\Resources\Dashboards\DashboardResource;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Dashboard;
use App\Models\DashboardFolder;
use Filament\Actions\Action;

class FolderDashboards extends Page
{
    protected static string $resource = DashboardResource::class;
    protected static ?string $title = 'Dashboards';
    protected string $view = 'filament.resources.dashboards.folder-dashboards';

    protected function getHeaderActions(): array
    {
        $folderId = request()->route('folder');
        $options = [];
        if ($folderId) {
            $user = Auth::user();
            $options = Dashboard::query()
                ->where('folder_id', $folderId)
                ->visibleTo($user)
                ->orderBy('title')
                ->pluck('title', 'id')
                ->toArray();
        }

        return [
            Action::make('create')
                ->label('Criar dashboard')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->url(function () use ($folderId) {
                    return DashboardResource::getUrl('create', [
                        'folder_id' => $folderId,
                    ]);
                })
                ->visible(function () use ($folderId) {
                    $user = Auth::user();
                    $isAdmin = $user && method_exists($user, 'hasRole') && ($user->hasRole('super_admin') || $user->hasRole('super-admin'));
                    $isManager = $user && method_exists($user, 'hasRole') && $user->hasRole('organization-manager');
                    return ! empty($folderId) && ($isAdmin || $isManager);
                }),
        ];
    }

    public function getHeading(): string
    {
        $folderId = request()->route('folder');
        $folder = $folderId ? DashboardFolder::find($folderId) : null;
        return $folder ? "Pastas / {$folder->name}" : 'Dashboards';
    }

    protected function getViewData(): array
    {
        $folderId = request()->route('folder');
        $user = Auth::user();
        $dashboards = Dashboard::query()
            ->where('folder_id', $folderId)
            ->visibleTo($user)
            ->orderBy('id')
            ->get();

        return [
            'dashboards' => $dashboards,
        ];
    }
}
