<?php

namespace App\Filament\Parent\Resources\BehaviorRecordResource\Pages;

use App\Filament\Parent\Resources\BehaviorRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBehaviorRecord extends EditRecord
{
    protected static string $resource = BehaviorRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
