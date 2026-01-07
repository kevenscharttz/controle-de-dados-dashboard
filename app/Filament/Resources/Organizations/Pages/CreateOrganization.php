<?php

namespace App\Filament\Resources\Organizations\Pages;

use App\Filament\Resources\Organizations\OrganizationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateOrganization extends CreateRecord
{
    protected static string $resource = OrganizationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Garantir que o logo_url seja salvo corretamente
        if (isset($data['logo_url']) && is_array($data['logo_url'])) {
            $data['logo_url'] = $data['logo_url'][0] ?? null;
        }
        
        // Garantir que o usuário atual seja associado à organização
        $user = Auth::user();
        if ($user && !isset($data['users'])) {
            $data['users'] = [$user->id];
        } elseif ($user && isset($data['users']) && is_array($data['users'])) {
            // Se já tem usuários selecionados, adiciona o usuário atual se não estiver
            if (!in_array($user->id, $data['users'])) {
                $data['users'][] = $user->id;
            }
        }
        
        return $data;
    }
}