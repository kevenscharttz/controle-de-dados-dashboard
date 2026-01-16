<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class ObservatorioDeDados extends Cluster
{
    protected static ?string $navigationLabel = 'Observatório de Dados';
    // Must match parent type: BackedEnum|string|null
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Folder;
    protected static ?string $slug = 'observatorio-de-dados';
    protected static ?int $navigationSort = 10; // near top
}
