<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BehaviorRecord;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AttentionRequired extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Get students who need attention based on:
        // 1. Low attendance (< 80% in last 30 days)
        // 2. Multiple negative behavior incidents
        // 3. No recent attendance
        
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $sevenDaysAgo = Carbon::now()->subDays(7);

        return $table
            ->heading('Students Requiring Attention')
            ->description('Students with attendance or behavior concerns')
            ->query(
                User::query()
                    ->role('student')
                    ->with(['class'])
                    ->withCount([
                        'attendances as recent_attendance_count' => function (Builder $query) use ($thirtyDaysAgo) {
                            $query->where('date', '>=', $thirtyDaysAgo);
                        },
                        'behaviorRecords as negative_behavior_count' => function (Builder $query) use ($thirtyDaysAgo) {
                            $query->where('date', '>=', $thirtyDaysAgo)
                                  ->where('points', '<', 0);
                        },
                    ])
                    ->withMax('attendances as last_attendance_date', 'date')
                    ->having('recent_attendance_count', '<', 20) // Less than 80% attendance (30 days)
                    ->orHaving('negative_behavior_count', '>=', 3) // 3 or more negative behaviors
                    ->orderBy('recent_attendance_count', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Student')
                    ->searchable()
                    ->sortable()
                    ->url(fn (User $record): string => "/admin/users/{$record->id}/edit"),
                
                Tables\Columns\TextColumn::make('class.name')
                    ->label('Class')
                    ->sortable()
                    ->default('Not Assigned'),
                
                Tables\Columns\TextColumn::make('recent_attendance_count')
                    ->label('Attendance (30 days)')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 24 => 'success',
                        $state >= 20 => 'warning',
                        default => 'danger',
                    })
                    ->suffix(' days')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('last_attendance_date')
                    ->label('Last Attended')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state && Carbon::parse($state)->isAfter(Carbon::now()->subDays(3)) 
                        ? 'success' 
                        : 'danger'),
                
                Tables\Columns\TextColumn::make('negative_behavior_count')
                    ->label('Negative Behaviors')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state == 0 => 'success',
                        $state <= 2 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('concern_level')
                    ->label('Concern Level')
                    ->badge()
                    ->state(function (User $record): string {
                        $score = 0;
                        
                        // Attendance score (max 3 points)
                        if ($record->recent_attendance_count < 15) $score += 3;
                        elseif ($record->recent_attendance_count < 20) $score += 2;
                        elseif ($record->recent_attendance_count < 24) $score += 1;
                        
                        // Behavior score (max 3 points)
                        if ($record->negative_behavior_count >= 5) $score += 3;
                        elseif ($record->negative_behavior_count >= 3) $score += 2;
                        elseif ($record->negative_behavior_count >= 1) $score += 1;
                        
                        return match (true) {
                            $score >= 4 => 'Critical',
                            $score >= 2 => 'High',
                            default => 'Medium',
                        };
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Critical' => 'danger',
                        'High' => 'warning',
                        default => 'info',
                    })
                    ->sortable(),
            ])
            ->defaultSort('recent_attendance_count', 'asc')
            ->paginated([10, 25, 50]);
    }
}
