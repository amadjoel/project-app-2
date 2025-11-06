<?php

namespace App\Filament\Parent\Resources\IncidentLogResource\Pages;

use App\Filament\Parent\Resources\IncidentLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIncidentLogs extends ListRecords
{
    protected static string $resource = IncidentLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
