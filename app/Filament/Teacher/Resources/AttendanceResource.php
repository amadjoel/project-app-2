<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\AttendanceResource\Pages;
use App\Filament\Teacher\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    
    protected static ?string $navigationLabel = 'Attendance';
    
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        // Teachers can only see attendance records they created
        return parent::getEloquentQuery()
            ->where('teacher_id', Auth::id())
            ->with(['student', 'teacher']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label('Student')
                    ->options(function () {
                        // Get all students
                        return User::role('student')
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->label('Date')
                    ->default(now())
                    ->required()
                    ->maxDate(now()),
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
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('teacher_id')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'warning' => 'late',
                        'info' => 'excused',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'present',
                        'heroicon-o-x-circle' => 'absent',
                        'heroicon-o-clock' => 'late',
                        'heroicon-o-document-check' => 'excused',
                    ]),
                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Check In')
                    ->time('H:i')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('check_out_time')
                    ->label('Check Out')
                    ->time('H:i')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'excused' => 'Excused',
                    ]),
                Tables\Filters\SelectFilter::make('student')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_all_csv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $records = Attendance::with('student')
                            ->where('teacher_id', Auth::id())
                            ->orderBy('date', 'desc')
                            ->get();

                        $filename = 'attendance-' . now()->format('Ymd-His') . '.csv';

                        return response()->streamDownload(function () use ($records) {
                            $handle = fopen('php://output', 'w');
                            // Header
                            fputcsv($handle, ['Date', 'Student', 'Status', 'Check In', 'Check Out', 'Notes']);
                            foreach ($records as $r) {
                                fputcsv($handle, [
                                    optional($r->date)->format('Y-m-d') ?? (string) $r->date,
                                    optional($r->student)->name,
                                    $r->status,
                                    $r->check_in_time,
                                    $r->check_out_time,
                                    $r->notes,
                                ]);
                            }
                            fclose($handle);
                        }, $filename, [
                            'Content-Type' => 'text/csv',
                        ]);
                    }),
                Tables\Actions\Action::make('export_all_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $records = Attendance::with('student')
                            ->where('teacher_id', Auth::id())
                            ->orderBy('date', 'desc')
                            ->get();

                        $pdf = Pdf::loadView('exports.attendance', [
                            'records' => $records,
                        ])->setPaper('a4', 'portrait');

                        $filename = 'attendance-' . now()->format('Ymd-His') . '.pdf';
                        return response()->streamDownload(fn () => print($pdf->output()), $filename, [
                            'Content-Type' => 'application/pdf',
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_selected_csv')
                        ->label('Export Selected CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            $filename = 'attendance-selected-' . now()->format('Ymd-His') . '.csv';
                            return response()->streamDownload(function () use ($records) {
                                $handle = fopen('php://output', 'w');
                                fputcsv($handle, ['Date', 'Student', 'Status', 'Check In', 'Check Out', 'Notes']);
                                foreach ($records as $r) {
                                    $r->loadMissing('student');
                                    fputcsv($handle, [
                                        optional($r->date)->format('Y-m-d') ?? (string) $r->date,
                                        optional($r->student)->name,
                                        $r->status,
                                        $r->check_in_time,
                                        $r->check_out_time,
                                        $r->notes,
                                    ]);
                                }
                                fclose($handle);
                            }, $filename, [
                                'Content-Type' => 'text/csv',
                            ]);
                        }),
                    Tables\Actions\BulkAction::make('export_selected_pdf')
                        ->label('Export Selected PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function ($records) {
                            $records->loadMissing('student');
                            $pdf = Pdf::loadView('exports.attendance', [
                                'records' => $records,
                            ])->setPaper('a4', 'portrait');
                            $filename = 'attendance-selected-' . now()->format('Ymd-His') . '.pdf';
                            return response()->streamDownload(fn () => print($pdf->output()), $filename, [
                                'Content-Type' => 'application/pdf',
                            ]);
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\DailyAttendance::route('/'),
            'list' => Pages\ListAttendances::route('/list'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
