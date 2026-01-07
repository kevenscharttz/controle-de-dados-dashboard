<?php

namespace App\Filament\Resources\Dashboards\Pages;

use App\Filament\Resources\Dashboards\DashboardResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use App\Models\Dashboard;
use App\Models\Report;

class CreateDashboard extends CreateRecord
{
    protected static string $resource = DashboardResource::class;

    // Nota: removida validação que impedia múltiplos dashboards por organização
    // A constraint UNIQUE em `organization_id` foi removida via migration, então
    // permitimos criar vários dashboards por organização.
    protected function beforeCreate(array $data = []): void
    {
        // intencionalmente vazio — validações específicas podem ser adicionadas aqui se necessário
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Garantir que o tipo tenha um valor padrão
        $data['type'] = $data['type'] ?? 'dashboard';
        
        // Criar no modelo correto baseado no tipo
        if ($data['type'] === 'report') {
            return Report::create($data);
        } else {
            return Dashboard::create($data);
        }
    }

    protected function getRedirectUrl(): string
    {
        $record = $this->getRecord();
        
        // Redirecionar para a lista correta baseada no tipo
        if ($record->getTable() === 'reports') {
            // Use o ReportResource helper para obter a URL da lista — evita depender do nome da rota
            return \App\Filament\Resources\Reports\ReportResource::getUrl('index');
        }

        return $this->getResource()::getUrl('index');
    }
}
