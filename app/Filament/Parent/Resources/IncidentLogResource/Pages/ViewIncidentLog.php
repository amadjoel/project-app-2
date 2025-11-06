<?php

namespace App\Filament\Parent\Resources\IncidentLogResource\Pages;

use App\Filament\Parent\Resources\IncidentLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewIncidentLog extends ViewRecord
{
    protected static string $resource = IncidentLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
