<?php

namespace App\Filament\Teacher\Resources\AttendanceResource\Widgets;

use Filament\Widgets\ChartWidget;

class AttendanceStatsOverview extends ChartWidget
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
        return 'line';
    }
}
