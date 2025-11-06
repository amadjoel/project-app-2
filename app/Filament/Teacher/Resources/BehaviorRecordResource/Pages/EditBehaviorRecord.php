<?php

namespace App\Filament\Teacher\Resources\BehaviorRecordResource\Pages;

use App\Filament\Teacher\Resources\BehaviorRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBehaviorRecord extends EditRecord
{
    protected static string $resource = BehaviorRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
