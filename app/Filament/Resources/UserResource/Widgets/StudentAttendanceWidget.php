<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\Attendance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class StudentAttendanceWidget extends BaseWidget
{
    public $record = null;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Attendance Records';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::query()
                    ->where('student_id', $this->record?->id)
                    ->orderBy('date', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Time In')
                    ->time('h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out_time')
                    ->label('Time Out')
                    ->time('h:i A')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'warning' => 'late',
                        'secondary' => 'excused',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->toggleable(),
            ])
            ->defaultSort('date', 'desc')
            ->paginated([10, 25, 50]);
    }
}
