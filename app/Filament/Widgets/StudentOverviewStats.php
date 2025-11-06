<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\ClassModel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentOverviewStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalStudents = User::role('student')->count();
        $totalTeachers = User::role('teacher')->count();
        $totalParents = User::role('parent')->count();
        $totalClasses = ClassModel::where('is_active', true)->count();
        
        $studentsWithClass = User::role('student')->whereNotNull('class_id')->count();
        $studentsWithoutClass = $totalStudents - $studentsWithClass;
        
        $teachersAssigned = ClassModel::whereNotNull('teacher_id')->distinct('teacher_id')->count();
        $teachersUnassigned = $totalTeachers - $teachersAssigned;

        return [
            Stat::make('Total Students', $totalStudents)
                ->description($studentsWithClass . ' assigned to classes')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success')
                ->chart([15, 20, 25, 30, 28, 30, 30]),

            Stat::make('Total Classes', $totalClasses)
                ->description('Avg ' . ($totalClasses > 0 ? round($totalStudents / $totalClasses, 1) : 0) . ' students per class')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Teachers', $totalTeachers)
                ->description($teachersAssigned . ' assigned, ' . $teachersUnassigned . ' available')
                ->descriptionIcon('heroicon-m-user')
                ->color('warning'),

            Stat::make('Parents', $totalParents)
                ->description('Registered guardians')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}
