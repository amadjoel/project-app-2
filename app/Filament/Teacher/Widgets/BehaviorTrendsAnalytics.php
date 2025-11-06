<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\BehaviorRecord;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BehaviorTrendsAnalytics extends Widget
{
    protected static string $view = 'filament.teacher.widgets.behavior-trends-analytics';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';
    
    public function getAnalytics(): array
    {
        $teacherId = Auth::id();
        $today = Carbon::today();
        
        // Get data for the last 30 days
        $startDate = $today->copy()->subDays(29);
        
        $allRecords = BehaviorRecord::where('teacher_id', $teacherId)
            ->where('date', '>=', $startDate)
            ->get();
        
        // Trend Analysis - Daily behavior counts
        $dailyTrends = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $dayRecords = $allRecords->where('date', $date->format('Y-m-d'));
            
            $dailyTrends[] = [
                'date' => $date->format('M d'),
                'positive' => $dayRecords->where('type', 'positive')->count(),
                'negative' => $dayRecords->where('type', 'negative')->count(),
                'neutral' => $dayRecords->where('type', 'neutral')->count(),
            ];
        }
        
        // Category Distribution (last 30 days)
        $categoryStats = [];
        $categories = ['participation', 'cooperation', 'respect', 'responsibility', 'leadership', 
                      'conflict', 'disruption', 'rule_violation', 'other'];
        
        foreach ($categories as $category) {
            $count = $allRecords->where('category', $category)->count();
            if ($count > 0) {
                $categoryStats[] = [
                    'category' => $this->formatCategory($category),
                    'count' => $count,
                    'type' => $this->getCategoryType($category),
                ];
            }
        }
        
        // Sort by count descending
        usort($categoryStats, fn($a, $b) => $b['count'] <=> $a['count']);
        
        // Student Behavior Patterns
        $studentPatterns = $this->analyzeStudentPatterns($allRecords);
        
        // Time-of-day patterns
        $timePatterns = $this->analyzeTimePatterns($allRecords);
        
        // Week-over-week comparison
        $weekComparison = $this->analyzeWeeklyTrends($teacherId, $today);
        
        // AI-generated insights
        $insights = $this->generateAIInsights(
            $allRecords,
            $dailyTrends,
            $categoryStats,
            $studentPatterns,
            $timePatterns,
            $weekComparison
        );
        
        return [
            'daily_trends' => $dailyTrends,
            'category_stats' => $categoryStats,
            'student_patterns' => $studentPatterns,
            'time_patterns' => $timePatterns,
            'week_comparison' => $weekComparison,
            'insights' => $insights,
        ];
    }
    
    protected function analyzeStudentPatterns($records): array
    {
        $studentData = [];
        
        foreach ($records->groupBy('student_id') as $studentId => $studentRecords) {
            $positive = $studentRecords->where('type', 'positive')->count();
            $negative = $studentRecords->where('type', 'negative')->count();
            $totalPoints = $studentRecords->sum('points');
            
            if ($positive > 0 || $negative > 0) {
                $student = \App\Models\User::find($studentId);
                $studentData[] = [
                    'name' => $student->name,
                    'positive' => $positive,
                    'negative' => $negative,
                    'points' => $totalPoints,
                    'trend' => $positive > $negative ? 'improving' : ($negative > $positive ? 'concern' : 'stable'),
                ];
            }
        }
        
        // Sort by points descending
        usort($studentData, fn($a, $b) => $b['points'] <=> $a['points']);
        
        return array_slice($studentData, 0, 10); // Top 10
    }
    
    protected function analyzeTimePatterns($records): array
    {
        $timeSlots = [
            'morning' => ['start' => 6, 'end' => 12, 'label' => 'Morning (6AM-12PM)'],
            'afternoon' => ['start' => 12, 'end' => 17, 'label' => 'Afternoon (12PM-5PM)'],
            'evening' => ['start' => 17, 'end' => 21, 'label' => 'Evening (5PM-9PM)'],
        ];
        
        $patterns = [];
        
        foreach ($timeSlots as $slot => $config) {
            $slotRecords = $records->filter(function ($record) use ($config) {
                $hour = Carbon::parse($record->time)->hour;
                return $hour >= $config['start'] && $hour < $config['end'];
            });
            
            $patterns[] = [
                'period' => $config['label'],
                'positive' => $slotRecords->where('type', 'positive')->count(),
                'negative' => $slotRecords->where('type', 'negative')->count(),
                'total' => $slotRecords->count(),
            ];
        }
        
        return $patterns;
    }
    
    protected function analyzeWeeklyTrends($teacherId, $today): array
    {
        $thisWeekStart = $today->copy()->startOfWeek();
        $lastWeekStart = $today->copy()->subWeek()->startOfWeek();
        $lastWeekEnd = $today->copy()->subWeek()->endOfWeek();
        
        $thisWeek = BehaviorRecord::where('teacher_id', $teacherId)
            ->where('date', '>=', $thisWeekStart)
            ->get();
            
        $lastWeek = BehaviorRecord::where('teacher_id', $teacherId)
            ->whereBetween('date', [$lastWeekStart, $lastWeekEnd])
            ->get();
        
        return [
            'this_week' => [
                'positive' => $thisWeek->where('type', 'positive')->count(),
                'negative' => $thisWeek->where('type', 'negative')->count(),
                'total_points' => $thisWeek->sum('points'),
            ],
            'last_week' => [
                'positive' => $lastWeek->where('type', 'positive')->count(),
                'negative' => $lastWeek->where('type', 'negative')->count(),
                'total_points' => $lastWeek->sum('points'),
            ],
        ];
    }
    
    protected function generateAIInsights(
        $allRecords,
        $dailyTrends,
        $categoryStats,
        $studentPatterns,
        $timePatterns,
        $weekComparison
    ): array {
        $insights = [];
        
        // Overall trend insight
        $recentDays = array_slice($dailyTrends, -7); // Last 7 days
        $recentPositive = array_sum(array_column($recentDays, 'positive'));
        $recentNegative = array_sum(array_column($recentDays, 'negative'));
        
        if ($recentPositive > $recentNegative * 2) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'heroicon-o-trending-up',
                'title' => 'Positive Trend Detected',
                'message' => "Your class is showing strong positive behavior patterns. Positive behaviors outnumber negative ones by 2:1 this week.",
            ];
        } elseif ($recentNegative > $recentPositive) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'heroicon-o-trending-down',
                'title' => 'Increasing Negative Behaviors',
                'message' => "Negative behaviors have increased recently. Consider reviewing classroom management strategies.",
            ];
        }
        
        // Category-specific insights
        if (!empty($categoryStats)) {
            $topCategory = $categoryStats[0];
            
            if ($topCategory['type'] === 'positive') {
                $insights[] = [
                    'type' => 'info',
                    'icon' => 'heroicon-o-chart-bar',
                    'title' => 'Top Positive Category',
                    'message' => "'{$topCategory['category']}' is your most recorded positive behavior with {$topCategory['count']} instances. Keep reinforcing this!",
                ];
            } else {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'heroicon-o-exclamation-circle',
                    'title' => 'Behavior Pattern Alert',
                    'message' => "'{$topCategory['category']}' appears most frequently with {$topCategory['count']} instances. Consider targeted interventions.",
                ];
            }
        }
        
        // Time pattern insights
        $maxTimeSlot = null;
        $maxNegative = 0;
        foreach ($timePatterns as $pattern) {
            if ($pattern['negative'] > $maxNegative) {
                $maxNegative = $pattern['negative'];
                $maxTimeSlot = $pattern['period'];
            }
        }
        
        if ($maxTimeSlot && $maxNegative > 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-clock',
                'title' => 'Time Pattern Identified',
                'message' => "Most negative behaviors occur during {$maxTimeSlot}. Consider adjusting activities or breaks during this time.",
            ];
        }
        
        // Student-specific insights
        $concernStudents = array_filter($studentPatterns, fn($s) => $s['trend'] === 'concern');
        if (count($concernStudents) > 0) {
            $names = implode(', ', array_column(array_slice($concernStudents, 0, 3), 'name'));
            $insights[] = [
                'type' => 'warning',
                'icon' => 'heroicon-o-user-group',
                'title' => 'Students Requiring Support',
                'message' => "{$names} " . (count($concernStudents) > 3 ? 'and others ' : '') . "show concerning behavior patterns. Consider individual interventions.",
            ];
        }
        
        $improvingStudents = array_filter($studentPatterns, fn($s) => $s['trend'] === 'improving');
        if (count($improvingStudents) > 0) {
            $names = implode(', ', array_column(array_slice($improvingStudents, 0, 3), 'name'));
            $insights[] = [
                'type' => 'success',
                'icon' => 'heroicon-o-arrow-trending-up',
                'title' => 'Improvement Recognized',
                'message' => "{$names} " . (count($improvingStudents) > 3 ? 'and others ' : '') . "are showing positive improvement. Keep encouraging them!",
            ];
        }
        
        // Weekly comparison insights
        $pointsChange = $weekComparison['this_week']['total_points'] - $weekComparison['last_week']['total_points'];
        if (abs($pointsChange) > 5) {
            if ($pointsChange > 0) {
                $insights[] = [
                    'type' => 'success',
                    'icon' => 'heroicon-o-arrow-up-circle',
                    'title' => 'Weekly Improvement',
                    'message' => "Overall behavior points increased by {$pointsChange} compared to last week. Great progress!",
                ];
            } else {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'heroicon-o-arrow-down-circle',
                    'title' => 'Weekly Decline',
                    'message' => "Behavior points decreased by " . abs($pointsChange) . " this week. Review recent changes in classroom dynamics.",
                ];
            }
        }
        
        // If no insights, provide default
        if (empty($insights)) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-light-bulb',
                'title' => 'Stable Patterns',
                'message' => "Behavior patterns are relatively stable. Continue monitoring for emerging trends.",
            ];
        }
        
        return $insights;
    }
    
    protected function formatCategory($category): string
    {
        return match($category) {
            'participation' => 'Participation',
            'cooperation' => 'Cooperation',
            'respect' => 'Respect',
            'responsibility' => 'Responsibility',
            'leadership' => 'Leadership',
            'conflict' => 'Conflict',
            'disruption' => 'Disruption',
            'rule_violation' => 'Rule Violation',
            'other' => 'Other',
            default => ucfirst($category),
        };
    }
    
    protected function getCategoryType($category): string
    {
        return in_array($category, ['participation', 'cooperation', 'respect', 'responsibility', 'leadership']) 
            ? 'positive' 
            : 'negative';
    }
}
