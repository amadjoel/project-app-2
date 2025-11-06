<?php

namespace App\Filament\Teacher\Resources\BehaviorRecordResource\Pages;

use App\Filament\Teacher\Resources\BehaviorRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBehaviorRecord extends CreateRecord
{
    protected static string $resource = BehaviorRecordResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['teacher_id'] = Auth::id();
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
