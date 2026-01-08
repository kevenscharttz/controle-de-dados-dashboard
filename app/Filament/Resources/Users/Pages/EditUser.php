<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                // Evita 500 no render caso policy/BD lancem excecao: trata como nao visivel
                ->visible(fn () => rescue(fn () => \Illuminate\Support\Facades\Gate::allows('delete', $this->record), false)),
        ];
    }
}
