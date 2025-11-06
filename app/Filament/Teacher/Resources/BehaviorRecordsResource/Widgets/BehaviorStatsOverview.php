<?php

namespace App\Filament\Teacher\Resources\BehaviorRecordsResource\Widgets;

use Filament\Widgets\ChartWidget;

class BehaviorStatsOverview extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
