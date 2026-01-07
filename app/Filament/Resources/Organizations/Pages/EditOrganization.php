<?php

namespace App\Filament\Resources\Organizations\Pages;

use App\Filament\Resources\Organizations\OrganizationResource;
use Filament\Resources\Pages\EditRecord;

class EditOrganization extends EditRecord
{
    protected static string $resource = OrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ViewAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Garantir que o logo_url seja salvo corretamente
        if (isset($data['logo_url']) && is_array($data['logo_url'])) {
            $data['logo_url'] = $data['logo_url'][0] ?? null;
        }
        
        return $data;
    }
}