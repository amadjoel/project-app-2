<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        if (auth()->id() === $this->record->id) {
            return [];
        }
        
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        $widgets = [];
        
        // Show attendance widget for students
        if ($this->record->hasRole('student')) {
            $widgets[] = UserResource\Widgets\StudentAttendanceWidget::class;
        }
        
        // Show children widget for parents
        if ($this->record->hasRole('parent')) {
            $widgets[] = UserResource\Widgets\ParentChildrenWidget::class;
        }
        
        return $widgets;
    }
}
