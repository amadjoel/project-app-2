<?php

namespace App\Filament\Parent\Resources\AuthorizedPickupResource\Pages;

use App\Filament\Parent\Resources\AuthorizedPickupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAuthorizedPickup extends EditRecord
{
    protected static string $resource = AuthorizedPickupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
