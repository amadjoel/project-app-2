<?php

namespace App\Filament\Resources\ClassModelResource\Pages;

use App\Filament\Resources\ClassModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewClassModel extends ViewRecord
{
    protected static string $resource = ClassModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            ClassModelResource\Widgets\ClassStudentsWidget::class,
        ];
    }
}
