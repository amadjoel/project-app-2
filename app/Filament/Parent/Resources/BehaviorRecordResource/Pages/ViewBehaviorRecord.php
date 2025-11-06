<?php

namespace App\Filament\Parent\Resources\BehaviorRecordResource\Pages;

use App\Filament\Parent\Resources\BehaviorRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBehaviorRecord extends ViewRecord
{
    protected static string $resource = BehaviorRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
