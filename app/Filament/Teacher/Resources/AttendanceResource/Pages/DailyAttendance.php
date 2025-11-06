<?php

namespace App\Filament\Teacher\Resources\AttendanceResource\Pages;

use App\Filament\Teacher\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DailyAttendance extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = AttendanceResource::class;

    protected static string $view = 'filament.teacher.resources.attendance-resource.pages.daily-attendance';
    
    protected static ?string $title = 'Daily Attendance';
    
    protected static ?string $navigationLabel = 'Daily View';
    
    protected static ?string $pollingInterval = '10s';
    
    public $selectedDate;
    
    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::role('student')
                    ->whereIn('users.class_id', function ($query) {
                        // Show students in classes taught by this teacher
                        $query->select('id')
                              ->from('classes')
                              ->where('teacher_id', Auth::id());
                    })
                    ->with(['attendances' => function ($query) {
                        $query->where('date', $this->selectedDate);
                    }])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('attendance_status')
                    ->label('Status')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->leftJoin('attendances as att_sort', function ($join) {
                            $join->on('users.id', '=', 'att_sort.student_id')
                                 ->where('att_sort.date', $this->selectedDate);
                        })
                        ->orderBy('att_sort.status', $direction)
                        ->select('users.*');
                    })
                    ->getStateUsing(function ($record) {
                        $attendance = $record->attendances->first();
                        return $attendance?->status ?? 'unmarked';
                    })
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'warning' => 'late',
                        'info' => 'excused',
                        'secondary' => 'unmarked',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'present',
                        'heroicon-o-x-circle' => 'absent',
                        'heroicon-o-clock' => 'late',
                        'heroicon-o-document-check' => 'excused',
                        'heroicon-o-question-mark-circle' => 'unmarked',
                    ]),
                Tables\Columns\TextColumn::make('attendances.check_in_time')
                    ->label('Check In')
                    ->getStateUsing(function ($record) {
                        $attendance = $record->attendances->first();
                        return $attendance?->check_in_time?->format('H:i') ?? '-';
                    }),
                Tables\Columns\TextColumn::make('attendances.check_out_time')
                    ->label('Check Out')
                    ->getStateUsing(function ($record) {
                        $attendance = $record->attendances->first();
                        return $attendance?->check_out_time?->format('H:i') ?? '-';
                    }),
                Tables\Columns\TextColumn::make('attendances.notes')
                    ->label('Notes')
                    ->limit(30)
                    ->getStateUsing(function ($record) {
                        $attendance = $record->attendances->first();
                        return $attendance?->notes ?? '-';
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_attendance')
                    ->label('Mark')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'present' => 'Present',
                                'absent' => 'Absent',
                                'late' => 'Late',
                                'excused' => 'Excused',
                            ])
                            ->default('present')
                            ->required(),
                        Forms\Components\TimePicker::make('check_in_time')
                            ->label('Check In Time')
                            ->seconds(false),
                        Forms\Components\TimePicker::make('check_out_time')
                            ->label('Check Out Time')
                            ->seconds(false),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2),
                    ])
                    ->action(function (User $record, array $data) {
                        Attendance::updateOrCreate(
                            [
                                'student_id' => $record->id,
                                'date' => $this->selectedDate,
                            ],
                            [
                                'teacher_id' => Auth::id(),
                                'status' => $data['status'],
                                'check_in_time' => $data['check_in_time'] ?? null,
                                'check_out_time' => $data['check_out_time'] ?? null,
                                'notes' => $data['notes'] ?? null,
                            ]
                        );
                    })
                    ->modalHeading(fn (User $record) => 'Mark Attendance for ' . $record->name)
                    ->fillForm(function (User $record) {
                        $attendance = $record->attendances->first();
                        return [
                            'status' => $attendance?->status ?? 'present',
                            'check_in_time' => $attendance?->check_in_time,
                            'check_out_time' => $attendance?->check_out_time,
                            'notes' => $attendance?->notes,
                        ];
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_present')
                    ->label('Mark as Present')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes (optional)')
                            ->rows(2)
                            ->placeholder('Add any notes for these students...'),
                    ])
                    ->action(function ($records, array $data) {
                        $currentTime = now()->format('H:i');
                        foreach ($records as $record) {
                            Attendance::updateOrCreate(
                                [
                                    'student_id' => $record->id,
                                    'date' => $this->selectedDate,
                                ],
                                [
                                    'teacher_id' => Auth::id(),
                                    'status' => 'present',
                                    'check_in_time' => $currentTime,
                                    'notes' => $data['notes'] ?? null,
                                ]
                            );
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\BulkAction::make('mark_absent')
                    ->label('Mark as Absent')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes (optional)')
                            ->rows(2)
                            ->placeholder('Reason for absence...'),
                    ])
                    ->action(function ($records, array $data) {
                        $currentTime = now()->format('H:i');
                        foreach ($records as $record) {
                            Attendance::updateOrCreate(
                                [
                                    'student_id' => $record->id,
                                    'date' => $this->selectedDate,
                                ],
                                [
                                    'teacher_id' => Auth::id(),
                                    'status' => 'absent',
                                    'check_in_time' => $currentTime,
                                    'notes' => $data['notes'] ?? null,
                                ]
                            );
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\BulkAction::make('checkout')
                    ->label('Check Out')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('primary')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $attendance = Attendance::where('student_id', $record->id)
                                ->where('date', $this->selectedDate)
                                ->where('teacher_id', Auth::id())
                                ->first();
                            
                            if ($attendance) {
                                $attendance->update([
                                    'check_out_time' => now()->format('H:i'),
                                ]);
                            }
                        }
                    })
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->modalHeading('Check Out Selected Students')
                    ->modalDescription('This will set the check-out time to now for all selected students.')
                    ->modalSubmitActionLabel('Check Out'),
            ])
            ->headerActions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('export_csv')
                        ->label('Export as CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn () => route('teacher.exports.attendance.csv'))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('export_pdf')
                        ->label('Export as PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn () => route('teacher.exports.attendance.pdf'))
                        ->openUrlInNewTab(),
                ])
                    ->label('Export Attendance')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->button(),
                Tables\Actions\Action::make('previous_day')
                    ->label('Previous Day')
                    ->icon('heroicon-o-arrow-left')
                    ->action(function () {
                        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->format('Y-m-d');
                    }),
                Tables\Actions\Action::make('today')
                    ->label('Today')
                    ->icon('heroicon-o-calendar')
                    ->action(function () {
                        $this->selectedDate = now()->format('Y-m-d');
                    }),
                Tables\Actions\Action::make('next_day')
                    ->label('Next Day')
                    ->icon('heroicon-o-arrow-right')
                    ->action(function () {
                        $this->selectedDate = Carbon::parse($this->selectedDate)->addDay()->format('Y-m-d');
                    }),
            ]);
    }
    
    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
