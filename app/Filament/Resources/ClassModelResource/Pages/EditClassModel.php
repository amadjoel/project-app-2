<?php

namespace App\Filament\Resources\ClassModelResource\Pages;

use App\Filament\Resources\ClassModelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassModel extends EditRecord
{
    protected static string $resource = ClassModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            ClassModelResource\Widgets\ClassStudentsWidget::class,
        ];
    }
}
