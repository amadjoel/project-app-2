<?php

namespace App\Filament\Parent\Resources\AuthorizedPickupResource\Pages;

use App\Filament\Parent\Resources\AuthorizedPickupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAuthorizedPickups extends ListRecords
{
    protected static string $resource = AuthorizedPickupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
