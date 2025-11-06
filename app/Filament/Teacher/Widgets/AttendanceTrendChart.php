<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Attendance Trend (Last 30 Days)';
    
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $teacherId = Auth::id();
        $data = [];
        $labels = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }
            
            $labels[] = $date->format('M d');
            
            $attendance = Attendance::where('teacher_id', $teacherId)
                ->where('date', $date)
                ->get();
            
            $total = $attendance->count();
            $present = $attendance->where('status', 'present')->count();
            
            $data[] = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Attendance Rate (%)',
                    'data' => $data,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'ticks' => [
                        'callback' => "function(value) { return value + '%'; }",
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
}
