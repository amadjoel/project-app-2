<?php

namespace App\Filament\Parent\Resources\AuthorizedPickupResource\Pages;

use App\Filament\Parent\Resources\AuthorizedPickupResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAuthorizedPickup extends CreateRecord
{
    protected static string $resource = AuthorizedPickupResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = Auth::id();
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
