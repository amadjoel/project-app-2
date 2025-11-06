<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SchoolAttendanceOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        // Get today's attendance stats
        $todayAttendance = Attendance::whereDate('date', $today)->get();
        $todayPresent = $todayAttendance->where('status', 'present')->count();
        $todayLate = $todayAttendance->where('status', 'late')->count();
        $todayAbsent = $todayAttendance->where('status', 'absent')->count();
        $totalStudents = User::role('student')->count();
        
        // This week's stats
        $weekAttendance = Attendance::whereBetween('date', [$thisWeek, $today])->get();
        $weekPresent = $weekAttendance->whereIn('status', ['present', 'late'])->count();
        $weekAbsent = $weekAttendance->where('status', 'absent')->count();
        
        // This month's stats
        $monthAttendance = Attendance::whereBetween('date', [$thisMonth, $today])->get();
        $monthAvgRate = $monthAttendance->count() > 0 
            ? round(($monthAttendance->whereIn('status', ['present', 'late'])->count() / $monthAttendance->count()) * 100, 1)
            : 0;

        // Per class stats - find class with lowest attendance
        $classStats = ClassModel::withCount([
            'students',
            'students as present_today' => function ($query) use ($today) {
                $query->whereHas('attendances', function ($q) use ($today) {
                    $q->whereDate('date', $today)
                      ->whereIn('status', ['present', 'late']);
                });
            }
        ])->get();

        $lowestAttendanceClass = $classStats
            ->filter(fn($class) => $class->students_count > 0)
            ->sortBy(fn($class) => $class->students_count > 0 ? ($class->present_today / $class->students_count) : 0)
            ->first();

        // AI-generated insights
        $todayAttendanceRate = $totalStudents > 0 ? round(($todayPresent / $totalStudents) * 100, 1) : 0;
        $aiInsight = $this->generateAIInsight($todayAttendanceRate, $todayLate, $lowestAttendanceClass);
        $weekTrend = $this->calculateTrend($thisWeek, $today);

        return [
            Stat::make("Today's Attendance", "{$todayPresent}/{$totalStudents} Students")
                ->description("{$todayAttendanceRate}% attendance rate")
                ->descriptionIcon($todayAttendanceRate >= 90 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle')
                ->color($todayAttendanceRate >= 90 ? 'success' : ($todayAttendanceRate >= 75 ? 'warning' : 'danger'))
                ->chart($this->getWeeklyChart()),

            Stat::make('Late Arrivals Today', $todayLate)
                ->description($todayLate > 5 ? 'Higher than usual' : 'Normal range')
                ->descriptionIcon($todayLate > 5 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-minus')
                ->color($todayLate > 5 ? 'warning' : 'success'),

            Stat::make('Weekly Trend', $weekTrend['description'])
                ->description($weekTrend['message'])
                ->descriptionIcon($weekTrend['icon'])
                ->color($weekTrend['color']),

            Stat::make('AI Insight', $aiInsight['title'])
                ->description($aiInsight['description'])
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('info'),
        ];
    }

    private function generateAIInsight($attendanceRate, $lateCount, $lowestClass): array
    {
        if ($attendanceRate >= 95) {
            return [
                'title' => 'Excellent Attendance',
                'description' => 'School-wide attendance is exceptional. Keep maintaining current practices.',
            ];
        } elseif ($attendanceRate >= 85) {
            if ($lateCount > 5) {
                return [
                    'title' => 'Monitor Tardiness',
                    'description' => "Good attendance but {$lateCount} late arrivals detected. Consider parent communication.",
                ];
            }
            return [
                'title' => 'Good Attendance',
                'description' => 'Attendance is within acceptable range. Continue monitoring trends.',
            ];
        } elseif ($attendanceRate >= 75) {
            if ($lowestClass) {
                return [
                    'title' => 'Attention Needed',
                    'description' => "Class '{$lowestClass->name}' has lower attendance. Consider intervention.",
                ];
            }
            return [
                'title' => 'Below Target',
                'description' => 'Attendance below optimal. Review patterns and contact absent families.',
            ];
        } else {
            return [
                'title' => 'Immediate Action Required',
                'description' => 'Critical attendance levels. Schedule meetings with administrators and families.',
            ];
        }
    }

    private function calculateTrend($startDate, $endDate): array
    {
        $dailyRates = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($date->isWeekend()) continue;
            
            $dayTotal = Attendance::whereDate('date', $date)->count();
            $dayPresent = Attendance::whereDate('date', $date)
                ->whereIn('status', ['present', 'late'])
                ->count();
            
            if ($dayTotal > 0) {
                $dailyRates[] = ($dayPresent / $dayTotal) * 100;
            }
        }

        if (count($dailyRates) < 2) {
            return [
                'description' => 'Stable',
                'message' => 'Insufficient data for trend analysis',
                'icon' => 'heroicon-m-minus',
                'color' => 'gray',
            ];
        }

        $avgFirstHalf = array_sum(array_slice($dailyRates, 0, ceil(count($dailyRates) / 2))) / ceil(count($dailyRates) / 2);
        $avgSecondHalf = array_sum(array_slice($dailyRates, floor(count($dailyRates) / 2))) / ceil(count($dailyRates) / 2);
        $change = $avgSecondHalf - $avgFirstHalf;

        if ($change > 2) {
            return [
                'description' => 'Improving',
                'message' => sprintf('+%.1f%% from last week', $change),
                'icon' => 'heroicon-m-arrow-trending-up',
                'color' => 'success',
            ];
        } elseif ($change < -2) {
            return [
                'description' => 'Declining',
                'message' => sprintf('%.1f%% from last week', $change),
                'icon' => 'heroicon-m-arrow-trending-down',
                'color' => 'danger',
            ];
        } else {
            return [
                'description' => 'Stable',
                'message' => 'Consistent attendance this week',
                'icon' => 'heroicon-m-minus',
                'color' => 'success',
            ];
        }
    }

    private function getWeeklyChart(): array
    {
        $chart = [];
        $startDate = Carbon::now()->subDays(6);
        
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            if ($date->isWeekend()) {
                $chart[] = 0;
                continue;
            }
            
            $total = Attendance::whereDate('date', $date)->count();
            $present = Attendance::whereDate('date', $date)
                ->whereIn('status', ['present', 'late'])
                ->count();
            
            $chart[] = $total > 0 ? round(($present / $total) * 100) : 0;
        }
        
        return $chart;
    }
}
