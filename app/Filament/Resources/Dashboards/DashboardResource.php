<?php

namespace App\Filament\Resources\Dashboards;

use App\Filament\Resources\Dashboards\Pages\CreateDashboard;
use App\Filament\Resources\Dashboards\Pages\EditDashboard;
use App\Filament\Resources\Dashboards\Pages\ListDashboards;
use App\Filament\Resources\Dashboards\Pages\ViewDashboard;
use App\Filament\Resources\Dashboards\Schemas\DashboardForm;
use App\Filament\Resources\Dashboards\Schemas\DashboardInfolist;
use App\Filament\Resources\Dashboards\Tables\DashboardsTable;
use App\Models\Dashboard;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DashboardResource extends Resource
{
    protected static ?string $model = Dashboard::class;

    protected static ?string $navigationLabel = 'Dashboards';
    protected static ?string $modelLabel = 'dashboard';
    protected static ?string $pluralModelLabel = 'Dashboards';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBarSquare;
    protected static string|\UnitEnum|null $navigationGroup = 'Dados';
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return DashboardForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DashboardInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DashboardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDashboards::route('/'),
            'create' => CreateDashboard::route('/create'),
            'view' => ViewDashboard::route('/{record}'),
            'edit' => EditDashboard::route('/{record}/edit'),
        ];
    }

    /**
     * Show the navigation item if the user can view any dashboard OR
     * if there are dashboards visible to the current user (public / org / user scoped).
     */
    public static function canAccess(array $parameters = []): bool
    {
        // Prefer Filament guard, then request user, then default Auth facade
        $user = Filament::auth()?->user() ?? request()->user() ?? Auth::user();

        if (! $user) {
            return false;
        }

        // Mostrar o item de navegação para qualquer usuário autenticado;
        // a listagem em si será filtrada por scope/policy e pode ficar vazia.
        return true;
    }
}
