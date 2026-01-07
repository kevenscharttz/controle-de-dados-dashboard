<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class AddLogosToOrganizationsSeeder extends Seeder
{
    public function run(): void
    {
        // Copiar logo padrão para cada organização
        $defaultLogoPath = 'public/images/default-organization-logo.svg';
        
        $organizations = Organization::all();
        
        foreach ($organizations as $index => $organization) {
            $logoFileName = "logo-org-{$organization->id}.svg";
            $logoPath = "organizations/logos/{$logoFileName}";
            
            // Copiar o logo padrão
            if (Storage::exists($defaultLogoPath)) {
                Storage::copy($defaultLogoPath, "public/{$logoPath}");
                
                // Atualizar a organização com o logo (forçar atualização)
                $organization->logo_url = $logoPath;
                $organization->logo_alt_text = "Logo da {$organization->name}";
                $organization->logo_settings = [
                    'width' => 200,
                    'height' => 100
                ];
                $organization->save();
                
                $this->command->info("Logo adicionado para: {$organization->name}");
            }
        }
        
        $this->command->info('Logos adicionados com sucesso!');
    }
}
