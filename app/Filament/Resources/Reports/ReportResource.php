<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\Pages\CreateReport;
use App\Filament\Resources\Reports\Pages\EditReport;
use App\Filament\Resources\Reports\Pages\ListReports;
use App\Filament\Resources\Reports\Pages\ViewReport;
use App\Filament\Resources\Reports\Schemas\ReportForm;
use App\Filament\Resources\Reports\Schemas\ReportInfolist;
use App\Filament\Resources\Reports\Tables\ReportsTable;
use App\Models\Report;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationLabel = 'Relatórios';
    protected static ?string $modelLabel = 'relatório';
    protected static ?string $pluralModelLabel = 'Relatórios';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;
    protected static string|\UnitEnum|null $navigationGroup = 'Dados';
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ReportForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportsTable::configure($table);
    }

    /**
     * Escopar a query Eloquent para que usuários não vejam relatórios de outras organizações.
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if (! $user) {
            return $query;
        }

        // Super-admin vê tudo (aceitar hífen e underscore)
        if ($user->hasRole('super-admin') || $user->hasRole('super_admin')) {
            return $query;
        }

        // Caso contrário, filtrar pelos organization_id que o usuário pertence
        $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
        return $query->whereIn('organization_id', $orgIds ?: [0]);
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
            'index' => ListReports::route('/'),
            'create' => CreateReport::route('/create'),
            'view' => ViewReport::route('/{record}'),
            'edit' => EditReport::route('/{record}/edit'),
        ];
    }

    public static function canAccess(array $parameters = []): bool
    {
        $user = Filament::auth()?->user() ?? request()->user() ?? Auth::user();

        if (! $user) {
            return false;
        }

        // Todos os usuários autenticados podem acessar relatórios
        return true;
    }

    public static function canCreate(): bool
    {
        $user = Filament::auth()?->user() ?? request()->user() ?? Auth::user();
        if (! $user) {
            return false;
        }

        // Delegar à policy/Gate para decidir se o usuário pode criar
        return $user->can('create', \App\Models\Report::class);
    }
}
