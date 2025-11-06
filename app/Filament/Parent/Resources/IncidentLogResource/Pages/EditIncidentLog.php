<?php

namespace App\Filament\Parent\Resources\IncidentLogResource\Pages;

use App\Filament\Parent\Resources\IncidentLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIncidentLog extends EditRecord
{
    protected static string $resource = IncidentLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
