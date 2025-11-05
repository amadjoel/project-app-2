<?php

namespace App\Filament\Resources\RFIDCardResource\Pages;

use App\Filament\Resources\RFIDCardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRFIDCard extends EditRecord
{
    protected static string $resource = RFIDCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
