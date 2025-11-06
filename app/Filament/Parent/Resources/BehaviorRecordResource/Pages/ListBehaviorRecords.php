<?php

namespace App\Filament\Parent\Resources\BehaviorRecordResource\Pages;

use App\Filament\Parent\Resources\BehaviorRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBehaviorRecords extends ListRecords
{
    protected static string $resource = BehaviorRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
