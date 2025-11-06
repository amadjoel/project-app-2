<?php

namespace App\Filament\Parent\Widgets;

use App\Models\Attendance;
use App\Models\BehaviorRecord;
use App\Models\IncidentLog;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WeeklyAIReport extends Widget
{
    protected static ?string $heading = 'Weekly AI Report';

    protected static string $view = 'filament.parent.widgets.weekly-ai-report';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = [
        'sm' => 2,
        'md' => 2,
        'lg' => 2,
        'xl' => 2,
        '2xl' => 2,
    ];

    protected function getViewData(): array
    {
        $user = Auth::user();
        $childrenIds = $user?->students?->pluck('id') ?? collect();

        if ($childrenIds->isEmpty()) {
            return [
                'report' => null,
                'weekStart' => Carbon::now()->startOfWeek(),
                'weekEnd' => Carbon::now()->endOfWeek(),
            ];
        }

        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // Gather weekly data
        $attendance = Attendance::whereIn('student_id', $childrenIds)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get();

        $behavior = BehaviorRecord::whereIn('student_id', $childrenIds)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get();

        $incidents = IncidentLog::whereIn('student_id', $childrenIds)
            ->whereBetween('incident_date', [$weekStart, $weekEnd])
            ->get();

        // Calculate metrics
        $metrics = [
            'attendance' => [
                'present' => $attendance->where('status', 'present')->count(),
                'absent' => $attendance->where('status', 'absent')->count(),
                'late' => $attendance->where('status', 'late')->count(),
                'total' => $attendance->count(),
            ],
            'behavior' => [
                'positive' => $behavior->where('type', 'positive')->count(),
                'negative' => $behavior->where('type', 'negative')->count(),
                'totalPoints' => $behavior->sum('points'),
            ],
            'incidents' => [
                'total' => $incidents->count(),
                'resolved' => $incidents->where('resolved', true)->count(),
                'unresolved' => $incidents->where('resolved', false)->count(),
            ],
        ];

        // Generate AI-style report
        $report = $this->generateReport($user->students, $metrics);

        return [
            'report' => $report,
            'metrics' => $metrics,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
        ];
    }

    protected function generateReport($children, array $metrics): array
    {
        $childrenNames = $children->pluck('name')->join(', ', ' and ');
        
        // Overall summary
        $summary = $this->generateSummary($childrenNames, $metrics);
        
        // Attendance insight
        $attendanceInsight = $this->generateAttendanceInsight($metrics['attendance']);
        
        // Behavior insight
        $behaviorInsight = $this->generateBehaviorInsight($metrics['behavior']);
        
        // Incidents insight
        $incidentsInsight = $this->generateIncidentsInsight($metrics['incidents']);
        
        // Recommendations
        $recommendations = $this->generateRecommendations($metrics);

        return [
            'summary' => $summary,
            'insights' => [
                'attendance' => $attendanceInsight,
                'behavior' => $behaviorInsight,
                'incidents' => $incidentsInsight,
            ],
            'recommendations' => $recommendations,
        ];
    }

    protected function generateSummary(string $childrenNames, array $metrics): string
    {
        $attendanceRate = $metrics['attendance']['total'] > 0 
            ? round(($metrics['attendance']['present'] / $metrics['attendance']['total']) * 100) 
            : 0;
        
        $behaviorTrend = $metrics['behavior']['totalPoints'] > 0 ? 'positive' : ($metrics['behavior']['totalPoints'] < 0 ? 'concerning' : 'neutral');
        
        $performance = $attendanceRate >= 95 && $metrics['behavior']['totalPoints'] >= 0 && $metrics['incidents']['total'] == 0
            ? 'excellent'
            : ($attendanceRate >= 80 && $metrics['behavior']['totalPoints'] >= 0 
                ? 'good' 
                : 'needs attention');

        return "This week, {$childrenNames} demonstrated {$performance} overall performance with a {$attendanceRate}% attendance rate and a {$behaviorTrend} behavior trend.";
    }

    protected function generateAttendanceInsight(array $attendance): string
    {
        if ($attendance['total'] == 0) {
            return "No attendance records for this week yet.";
        }

        $rate = round(($attendance['present'] / $attendance['total']) * 100);
        
        if ($rate >= 95) {
            return "Excellent attendance with {$rate}% present. Keep up the great routine!";
        } elseif ($rate >= 80) {
            return "Good attendance at {$rate}%. " . ($attendance['late'] > 0 ? "Watch for {$attendance['late']} late arrivals." : "");
        } else {
            return "Attendance needs improvement at {$rate}%. Consider discussing any challenges with your child.";
        }
    }

    protected function generateBehaviorInsight(array $behavior): string
    {
        $total = $behavior['positive'] + $behavior['negative'];
        
        if ($total == 0) {
            return "No behavior records logged this week.";
        }

        $points = $behavior['totalPoints'];
        $positiveRatio = $total > 0 ? round(($behavior['positive'] / $total) * 100) : 0;

        if ($points > 10) {
            return "Outstanding behavior! Earned {$points} points with {$behavior['positive']} positive recognitions. Celebrate these achievements!";
        } elseif ($points > 0) {
            return "Good behavior overall with {$points} points earned. {$behavior['positive']} positive actions noted.";
        } elseif ($points == 0) {
            return "Behavior is balanced this week. Continue encouraging positive choices.";
        } else {
            return "Behavior challenges noted with {$points} points. {$behavior['negative']} incidents recorded. Consider discussing strategies with teachers.";
        }
    }

    protected function generateIncidentsInsight(array $incidents): string
    {
        if ($incidents['total'] == 0) {
            return "No incidents reported this week - excellent!";
        }

        if ($incidents['unresolved'] == 0) {
            return "All {$incidents['total']} incidents have been resolved successfully.";
        }

        return "{$incidents['total']} incident(s) this week, with {$incidents['unresolved']} still unresolved. Follow up with teachers for updates.";
    }

    protected function generateRecommendations(array $metrics): array
    {
        $recommendations = [];

        // Attendance recommendations
        if ($metrics['attendance']['total'] > 0) {
            $rate = ($metrics['attendance']['present'] / $metrics['attendance']['total']) * 100;
            if ($rate < 90) {
                $recommendations[] = "Establish a consistent morning routine to improve attendance.";
            }
            if ($metrics['attendance']['late'] > 2) {
                $recommendations[] = "Set earlier bedtime to reduce late arrivals.";
            }
        }

        // Behavior recommendations
        if ($metrics['behavior']['totalPoints'] < 0) {
            $recommendations[] = "Schedule a parent-teacher conference to discuss behavior strategies.";
        } elseif ($metrics['behavior']['positive'] > 5) {
            $recommendations[] = "Reinforce positive behaviors with praise and rewards at home.";
        }

        // Incident recommendations
        if ($metrics['incidents']['unresolved'] > 0) {
            $recommendations[] = "Contact teachers to resolve pending incidents.";
        }

        // Default recommendation
        if (empty($recommendations)) {
            $recommendations[] = "Keep up the great work! Maintain open communication with teachers.";
        }

        return $recommendations;
    }
}
