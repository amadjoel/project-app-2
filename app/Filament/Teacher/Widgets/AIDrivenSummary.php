<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\Attendance;
use App\Models\IncidentLog;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AIDrivenSummary extends Widget
{
    protected static string $view = 'filament.teacher.widgets.a-i-driven-summary';
    
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';
    
    public function getSummaryData(): array
    {
        $teacherId = Auth::id();
        $today = Carbon::today();
        
        // Get students assigned to this teacher
        $studentIds = Attendance::where('teacher_id', $teacherId)
            ->distinct()
            ->pluck('student_id');
        
        $totalStudents = $studentIds->count();
        
        // Attendance analytics
        $weekAttendance = Attendance::where('teacher_id', $teacherId)
            ->whereBetween('date', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()])
            ->get();
        
        $weekPresent = $weekAttendance->where('status', 'present')->count();
        $weekAbsent = $weekAttendance->where('status', 'absent')->count();
        $weekLate = $weekAttendance->where('status', 'late')->count();
        
        // Find students with attendance concerns (absent/late more than 2 times this week)
        $concernStudents = [];
        foreach ($studentIds as $studentId) {
            $studentAbsences = $weekAttendance->where('student_id', $studentId)
                ->whereIn('status', ['absent', 'late'])
                ->count();
            
            if ($studentAbsences >= 2) {
                $student = \App\Models\User::find($studentId);
                $concernStudents[] = $student->name;
            }
        }
        
        // Incident analytics
        $weekIncidents = IncidentLog::where('teacher_id', $teacherId)
            ->whereBetween('incident_date', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()])
            ->get();
        
        $unresolvedIncidents = $weekIncidents->where('resolved', false);
        $criticalIncidents = $weekIncidents->where('severity', 'critical');
        
        // Students with multiple incidents this week
        $incidentStudents = [];
        foreach ($studentIds as $studentId) {
            $studentIncidents = $weekIncidents->where('student_id', $studentId)->count();
            
            if ($studentIncidents >= 2) {
                $student = \App\Models\User::find($studentId);
                $incidentStudents[] = $student->name;
            }
        }
        
        // Generate AI-style insights
        $insights = $this->generateInsights(
            $totalStudents,
            $weekPresent,
            $weekAbsent,
            $weekLate,
            $concernStudents,
            $weekIncidents->count(),
            $unresolvedIncidents->count(),
            $criticalIncidents->count(),
            $incidentStudents
        );
        
        return [
            'insights' => $insights,
            'stats' => [
                'total_students' => $totalStudents,
                'week_present' => $weekPresent,
                'week_absent' => $weekAbsent,
                'week_late' => $weekLate,
                'week_incidents' => $weekIncidents->count(),
                'unresolved_incidents' => $unresolvedIncidents->count(),
            ],
        ];
    }
    
    protected function generateInsights(
        $totalStudents,
        $weekPresent,
        $weekAbsent,
        $weekLate,
        $concernStudents,
        $totalIncidents,
        $unresolvedIncidents,
        $criticalIncidents,
        $incidentStudents
    ): array {
        $insights = [];
        
        // Attendance insights
        if ($totalStudents > 0) {
            $attendanceRate = round(($weekPresent / ($weekPresent + $weekAbsent + $weekLate)) * 100, 1);
            
            if ($attendanceRate >= 95) {
                $insights[] = [
                    'type' => 'success',
                    'icon' => 'heroicon-o-check-circle',
                    'title' => 'Excellent Attendance',
                    'message' => "Your class has maintained a {$attendanceRate}% attendance rate this week. Keep up the great work!"
                ];
            } elseif ($attendanceRate >= 85) {
                $insights[] = [
                    'type' => 'info',
                    'icon' => 'heroicon-o-information-circle',
                    'title' => 'Good Attendance',
                    'message' => "Attendance is at {$attendanceRate}% this week. Consider reaching out to students who missed classes."
                ];
            } else {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'heroicon-o-exclamation-triangle',
                    'title' => 'Attendance Concern',
                    'message' => "Attendance has dropped to {$attendanceRate}% this week. Immediate action may be needed."
                ];
            }
        }
        
        // Students requiring attention
        if (count($concernStudents) > 0) {
            $studentList = implode(', ', array_slice($concernStudents, 0, 3));
            $remaining = count($concernStudents) > 3 ? ' and ' . (count($concernStudents) - 3) . ' more' : '';
            
            $insights[] = [
                'type' => 'warning',
                'icon' => 'heroicon-o-user-group',
                'title' => 'Students Needing Attention',
                'message' => "{$studentList}{$remaining} have had attendance issues this week. Consider reaching out to parents."
            ];
        }
        
        // Incident insights
        if ($criticalIncidents > 0) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'heroicon-o-shield-exclamation',
                'title' => 'Critical Incidents',
                'message' => "You have {$criticalIncidents} critical incident(s) that require immediate attention."
            ];
        } elseif ($unresolvedIncidents > 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'heroicon-o-exclamation-circle',
                'title' => 'Pending Incidents',
                'message' => "{$unresolvedIncidents} incident(s) are still unresolved. Review and take appropriate action."
            ];
        } elseif ($totalIncidents === 0) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'heroicon-o-check-badge',
                'title' => 'No Incidents',
                'message' => "Great week! No incidents reported. Your classroom management is effective."
            ];
        }
        
        // Students with behavior patterns
        if (count($incidentStudents) > 0) {
            $studentList = implode(', ', array_slice($incidentStudents, 0, 2));
            $remaining = count($incidentStudents) > 2 ? ' and ' . (count($incidentStudents) - 2) . ' more' : '';
            
            $insights[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-clipboard-document-list',
                'title' => 'Pattern Alert',
                'message' => "{$studentList}{$remaining} have had multiple incidents this week. Consider intervention strategies."
            ];
        }
        
        // If no specific insights, provide encouraging message
        if (empty($insights)) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-sparkles',
                'title' => 'All Systems Normal',
                'message' => "Everything looks good! Continue monitoring your class attendance and behavior patterns."
            ];
        }
        
        return $insights;
    }
}
