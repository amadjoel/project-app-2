<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\Attendance;
use App\Models\IncidentLog;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TeacherStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $teacherId = Auth::id();
        $today = Carbon::today();
        
        // Get students assigned to this teacher
        $studentIds = Attendance::where('teacher_id', $teacherId)
            ->distinct()
            ->pluck('student_id');
        
        $totalStudents = $studentIds->count();
        
        // Today's attendance
        $todayAttendance = Attendance::where('teacher_id', $teacherId)
            ->where('date', $today)
            ->get();
        
        $presentToday = $todayAttendance->where('status', 'present')->count();
        $absentToday = $todayAttendance->where('status', 'absent')->count();
        $lateToday = $todayAttendance->where('status', 'late')->count();
        
        $attendanceRate = $totalStudents > 0 ? round(($presentToday / $totalStudents) * 100, 1) : 0;
        
        // This week's attendance trend
        $lastWeekRate = $this->getWeekAttendanceRate($teacherId, Carbon::today()->subWeek());
        $attendanceTrend = $attendanceRate - $lastWeekRate;
        
        // Unresolved incidents
        $unresolvedIncidents = IncidentLog::where('teacher_id', $teacherId)
            ->where('resolved', false)
            ->count();
        
        // This month's incidents
        $monthIncidents = IncidentLog::where('teacher_id', $teacherId)
            ->whereMonth('incident_date', $today->month)
            ->whereYear('incident_date', $today->year)
            ->count();
        
        // Last month's incidents for comparison
        $lastMonthIncidents = IncidentLog::where('teacher_id', $teacherId)
            ->whereMonth('incident_date', $today->copy()->subMonth()->month)
            ->whereYear('incident_date', $today->copy()->subMonth()->year)
            ->count();
        
        $incidentTrend = $lastMonthIncidents > 0 
            ? round((($monthIncidents - $lastMonthIncidents) / $lastMonthIncidents) * 100, 1)
            : 0;

        return [
            Stat::make('Total Students', $totalStudents)
                ->description('Students in your class')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            
            Stat::make('Today\'s Attendance', $presentToday . ' / ' . $totalStudents)
                ->description($attendanceRate . '% attendance rate')
                ->descriptionIcon($attendanceTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($attendanceRate >= 90 ? 'success' : ($attendanceRate >= 75 ? 'warning' : 'danger'))
                ->chart($this->getAttendanceChartData($teacherId)),
            
            Stat::make('Absent Today', $absentToday)
                ->description($lateToday . ' students late')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($absentToday > 5 ? 'danger' : 'warning'),
            
            Stat::make('Unresolved Incidents', $unresolvedIncidents)
                ->description($monthIncidents . ' incidents this month')
                ->descriptionIcon($incidentTrend > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($unresolvedIncidents > 0 ? 'danger' : 'success'),
        ];
    }
    
    protected function getWeekAttendanceRate($teacherId, $date): float
    {
        $studentIds = Attendance::where('teacher_id', $teacherId)
            ->distinct()
            ->pluck('student_id');
        
        $totalStudents = $studentIds->count();
        
        if ($totalStudents === 0) {
            return 0;
        }
        
        $weekAttendance = Attendance::where('teacher_id', $teacherId)
            ->where('date', $date)
            ->where('status', 'present')
            ->count();
        
        return round(($weekAttendance / $totalStudents) * 100, 1);
    }
    
    protected function getAttendanceChartData($teacherId): array
    {
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            
            if ($date->isWeekend()) {
                $data[] = 0;
                continue;
            }
            
            $present = Attendance::where('teacher_id', $teacherId)
                ->where('date', $date)
                ->where('status', 'present')
                ->count();
            
            $data[] = $present;
        }
        
        return $data;
    }
}
