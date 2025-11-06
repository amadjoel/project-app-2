<?php

namespace App\Filament\Parent\Resources\IncidentLogResource\Pages;

use App\Filament\Parent\Resources\IncidentLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateIncidentLog extends CreateRecord
{
    protected static string $resource = IncidentLogResource::class;
}
