<?php

namespace App\Filament\Widgets;

use App\Models\BehaviorRecord;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class BehaviorTrends extends ChartWidget
{
    protected static ?string $heading = 'Behavior Trends (Last 7 Days)';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = [];
        $positive = [];
        $negative = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('M d');
            
            $dayRecords = BehaviorRecord::whereDate('date', $date)->get();
            $positive[] = $dayRecords->where('points', '>', 0)->sum('points');
            $negative[] = abs($dayRecords->where('points', '<', 0)->sum('points'));
        }

        return [
            'datasets' => [
                [
                    'label' => 'Positive Behaviors',
                    'data' => $positive,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                ],
                [
                    'label' => 'Negative Behaviors',
                    'data' => $negative,
                    'backgroundColor' => '#ef4444',
                    'borderColor' => '#ef4444',
                ],
            ],
            'labels' => $days,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
