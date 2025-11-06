<?php

namespace App\Filament\Parent\Widgets;

use App\Models\Attendance;
use App\Models\BehaviorRecord;
use App\Models\IncidentLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ParentOverviewStats extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $parent = Auth::user();
        $children = $parent->students;
        $childrenIds = $children->pluck('id');
        
        if ($childrenIds->isEmpty()) {
            return [
                Stat::make('Children', 0)
                    ->description('No children linked to your account')
                    ->color('gray'),
            ];
        }
        
        $today = Carbon::today();
        
        // Today's attendance summary
        $todayAttendance = Attendance::whereIn('student_id', $childrenIds)
            ->where('date', $today)
            ->get();
        
        $presentToday = $todayAttendance->where('status', 'present')->count();
        $absentToday = $todayAttendance->where('status', 'absent')->count();
        $lateToday = $todayAttendance->where('status', 'late')->count();
        
        // This week's unresolved incidents
        $weekStart = $today->copy()->startOfWeek();
        $unresolvedIncidents = IncidentLog::whereIn('student_id', $childrenIds)
            ->where('resolved', false)
            ->count();
        
        $weekIncidents = IncidentLog::whereIn('student_id', $childrenIds)
            ->where('incident_date', '>=', $weekStart)
            ->count();
        
        // Behavior points this week
        $weekBehavior = BehaviorRecord::whereIn('student_id', $childrenIds)
            ->where('date', '>=', $weekStart)
            ->get();
        
        $positiveCount = $weekBehavior->where('type', 'positive')->count();
        $negativeCount = $weekBehavior->where('type', 'negative')->count();
        $totalPoints = $weekBehavior->sum('points');
        
        return [
            Stat::make('My Children', $children->count())
                ->description($children->pluck('name')->join(', '))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            
            Stat::make('Today\'s Attendance', $presentToday . ' present')
                ->description(
                    ($absentToday > 0 ? "{$absentToday} absent" : 'All present') . 
                    ($lateToday > 0 ? ", {$lateToday} late" : '')
                )
                ->descriptionIcon($absentToday > 0 ? 'heroicon-m-exclamation-circle' : 'heroicon-m-check-circle')
                ->color($absentToday > 0 ? 'danger' : 'success'),
            
            Stat::make('Incidents This Week', $weekIncidents)
                ->description($unresolvedIncidents > 0 ? "{$unresolvedIncidents} unresolved" : 'All resolved')
                ->descriptionIcon($unresolvedIncidents > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-shield-check')
                ->color($unresolvedIncidents > 0 ? 'warning' : 'success'),
            
            Stat::make('Behavior Points', $totalPoints > 0 ? "+{$totalPoints}" : $totalPoints)
                ->description("{$positiveCount} positive, {$negativeCount} negative")
                ->descriptionIcon($totalPoints > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($totalPoints > 0 ? 'success' : ($totalPoints < 0 ? 'danger' : 'gray')),
            
        ];
    }
}

