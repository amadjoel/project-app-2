<?php

namespace App\Filament\Parent\Widgets;

use App\Models\Attendance;
use App\Models\IncidentLog;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DailyActivitySummary extends Widget
{
    protected static ?string $heading = 'Daily Activity Summary';

    protected static string $view = 'filament.parent.widgets.daily-activity-summary';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected function getViewData(): array
    {
        $user = Auth::user();
        $childrenIds = $user?->students?->pluck('id') ?? collect();

        if ($childrenIds->isEmpty()) {
            return [
                'children' => collect(),
                'attendance' => [
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                    'excused' => 0,
                ],
                'incidents' => [
                    'total' => 0,
                    'unresolved' => 0,
                ],
            ];
        }

        $today = Carbon::today();

        // Today's attendance
        $todayAttendance = Attendance::whereIn('student_id', $childrenIds)
            ->where('date', $today)
            ->get();

        $attendance = [
            'present' => $todayAttendance->where('status', 'present')->count(),
            'absent' => $todayAttendance->where('status', 'absent')->count(),
            'late' => $todayAttendance->where('status', 'late')->count(),
            'excused' => $todayAttendance->where('status', 'excused')->count(),
        ];

        // Today's incidents
        $todayIncidents = IncidentLog::whereIn('student_id', $childrenIds)
            ->where('incident_date', $today)
            ->get();

        $incidents = [
            'total' => $todayIncidents->count(),
            'unresolved' => $todayIncidents->where('resolved', false)->count(),
        ];

        return [
            'children' => $user->students,
            'attendance' => $attendance,
            'incidents' => $incidents,
        ];
    }
}
