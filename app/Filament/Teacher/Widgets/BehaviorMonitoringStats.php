<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\BehaviorRecord;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class BehaviorMonitoringStats extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected function getStats(): array
    {
        $teacherId = Auth::id();
        
        // This week's data
        $thisWeekStart = now()->startOfWeek();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();
        
        // Total positive behaviors this week
        $positiveThisWeek = BehaviorRecord::where('teacher_id', $teacherId)
            ->where('type', 'positive')
            ->where('date', '>=', $thisWeekStart)
            ->count();
            
        $positiveLastWeek = BehaviorRecord::where('teacher_id', $teacherId)
            ->where('type', 'positive')
            ->whereBetween('date', [$lastWeekStart, $lastWeekEnd])
            ->count();
            
        $positiveTrend = $positiveLastWeek > 0 
            ? (($positiveThisWeek - $positiveLastWeek) / $positiveLastWeek) * 100 
            : 0;
        
        // Total negative behaviors this week
        $negativeThisWeek = BehaviorRecord::where('teacher_id', $teacherId)
            ->where('type', 'negative')
            ->where('date', '>=', $thisWeekStart)
            ->count();
            
        $negativeLastWeek = BehaviorRecord::where('teacher_id', $teacherId)
            ->where('type', 'negative')
            ->whereBetween('date', [$lastWeekStart, $lastWeekEnd])
            ->count();
            
        $negativeTrend = $negativeLastWeek > 0 
            ? (($negativeThisWeek - $negativeLastWeek) / $negativeLastWeek) * 100 
            : 0;
        
        // Total points accumulated this week
        $pointsThisWeek = BehaviorRecord::where('teacher_id', $teacherId)
            ->where('date', '>=', $thisWeekStart)
            ->sum('points');
            
        $pointsLastWeek = BehaviorRecord::where('teacher_id', $teacherId)
            ->whereBetween('date', [$lastWeekStart, $lastWeekEnd])
            ->sum('points');
            
        $pointsTrend = $pointsLastWeek != 0 
            ? (($pointsThisWeek - $pointsLastWeek) / abs($pointsLastWeek)) * 100 
            : 0;
        
        // Pending follow-ups
        $pendingFollowups = BehaviorRecord::where('teacher_id', $teacherId)
            ->where('requires_followup', true)
            ->whereNull('followup_completed_at')
            ->count();
            
        $totalFollowups = BehaviorRecord::where('teacher_id', $teacherId)
            ->where('requires_followup', true)
            ->count();
        
        return [
            Stat::make('Positive Behaviors', $positiveThisWeek)
                ->description(abs($positiveTrend) > 0 ? round($positiveTrend, 1) . '% from last week' : 'Same as last week')
                ->descriptionIcon($positiveTrend > 0 ? 'heroicon-m-arrow-trending-up' : ($positiveTrend < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus'))
                ->color('success')
                ->chart(array_map(function ($day) use ($teacherId) {
                    return BehaviorRecord::where('teacher_id', $teacherId)
                        ->where('type', 'positive')
                        ->whereDate('date', now()->subDays(6 - $day))
                        ->count();
                }, range(0, 6))),
            
            Stat::make('Negative Behaviors', $negativeThisWeek)
                ->description(abs($negativeTrend) > 0 ? round(abs($negativeTrend), 1) . '% from last week' : 'Same as last week')
                ->descriptionIcon($negativeTrend < 0 ? 'heroicon-m-arrow-trending-down' : ($negativeTrend > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-minus'))
                ->color($negativeTrend < 0 ? 'success' : 'danger')
                ->chart(array_map(function ($day) use ($teacherId) {
                    return BehaviorRecord::where('teacher_id', $teacherId)
                        ->where('type', 'negative')
                        ->whereDate('date', now()->subDays(6 - $day))
                        ->count();
                }, range(0, 6))),
            
            Stat::make('Total Points', $pointsThisWeek > 0 ? '+' . $pointsThisWeek : $pointsThisWeek)
                ->description('This week')
                ->color($pointsThisWeek > 0 ? 'success' : ($pointsThisWeek < 0 ? 'warning' : 'gray'))
                ->chart(array_map(function ($day) use ($teacherId) {
                    return BehaviorRecord::where('teacher_id', $teacherId)
                        ->whereDate('date', now()->subDays(6 - $day))
                        ->sum('points') ?? 0;
                }, range(0, 6))),
            
            Stat::make('Pending Follow-ups', $pendingFollowups)
                ->description($totalFollowups > 0 ? 'of ' . $totalFollowups . ' total' : 'No follow-ups needed')
                ->color($pendingFollowups > 0 ? 'warning' : 'success')
                ->icon($pendingFollowups > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'),
        ];
    }
}
