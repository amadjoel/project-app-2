<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\IncidentLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class IncidentsByTypeChart extends ChartWidget
{
    protected static ?string $heading = 'Incidents by Type (This Month)';
    
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $teacherId = Auth::id();
        
        $incidents = IncidentLog::where('teacher_id', $teacherId)
            ->whereMonth('incident_date', now()->month)
            ->whereYear('incident_date', now()->year)
            ->get();
        
        $types = [
            'behavioral' => 0,
            'academic' => 0,
            'safety' => 0,
            'health' => 0,
            'bullying' => 0,
            'other' => 0,
        ];
        
        foreach ($incidents as $incident) {
            $types[$incident->type]++;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Incidents',
                    'data' => array_values($types),
                    'backgroundColor' => [
                        '#3b82f6', // blue
                        '#f59e0b', // amber
                        '#ef4444', // red
                        '#06b6d4', // cyan
                        '#6b7280', // gray
                        '#9ca3af', // light gray
                    ],
                ],
            ],
            'labels' => ['Behavioral', 'Academic', 'Safety', 'Health', 'Bullying', 'Other'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
