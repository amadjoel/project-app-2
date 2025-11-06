<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\IncidentLogResource\Pages;
use App\Filament\Teacher\Resources\IncidentLogResource\RelationManagers;
use App\Models\IncidentLog;
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

class IncidentLogResource extends Resource
{
    protected static ?string $model = IncidentLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    
    protected static ?string $navigationLabel = 'Incident Logs';
    
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        // Teachers can only see incident logs they created
        return parent::getEloquentQuery()
            ->where('teacher_id', Auth::id())
            ->with(['student', 'teacher']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Incident Information')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Student')
                            ->options(function () {
                                return User::role('student')
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\DatePicker::make('incident_date')
                            ->label('Date')
                            ->default(now())
                            ->required()
                            ->maxDate(now()),
                        Forms\Components\TimePicker::make('incident_time')
                            ->label('Time')
                            ->default(now())
                            ->required()
                            ->seconds(false),
                        Forms\Components\Select::make('type')
                            ->label('Incident Type')
                            ->options([
                                'behavioral' => 'Behavioral',
                                'academic' => 'Academic',
                                'safety' => 'Safety',
                                'health' => 'Health',
                                'bullying' => 'Bullying',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->default('behavioral'),
                        Forms\Components\Select::make('severity')
                            ->label('Severity')
                            ->options([
                                'minor' => 'Minor',
                                'moderate' => 'Moderate',
                                'serious' => 'Serious',
                                'critical' => 'Critical',
                            ])
                            ->required()
                            ->default('minor'),
                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Brief summary of incident'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(4)
                            ->placeholder('Detailed description of what happened...'),
                        Forms\Components\Textarea::make('action_taken')
                            ->label('Action Taken')
                            ->rows(3)
                            ->placeholder('What actions were taken to address the incident...'),
                    ]),
                
                Forms\Components\Section::make('Parent Communication')
                    ->schema([
                        Forms\Components\Toggle::make('parent_notified')
                            ->label('Parent Notified')
                            ->default(false)
                            ->reactive(),
                        Forms\Components\DateTimePicker::make('parent_notified_at')
                            ->label('Notified At')
                            ->visible(fn (callable $get) => $get('parent_notified')),
                        Forms\Components\Textarea::make('parent_response')
                            ->label('Parent Response')
                            ->rows(3)
                            ->visible(fn (callable $get) => $get('parent_notified')),
                    ])->columns(1),
                
                Forms\Components\Section::make('Resolution')
                    ->schema([
                        Forms\Components\Toggle::make('resolved')
                            ->label('Resolved')
                            ->default(false)
                            ->reactive(),
                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->label('Resolved At')
                            ->visible(fn (callable $get) => $get('resolved')),
                    ])->columns(1),
                
                Forms\Components\Hidden::make('teacher_id')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('incident_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('incident_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('incident_time')
                    ->label('Time')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'behavioral',
                        'warning' => 'academic',
                        'danger' => 'safety',
                        'info' => 'health',
                        'secondary' => 'bullying',
                        'gray' => 'other',
                    ]),
                Tables\Columns\BadgeColumn::make('severity')
                    ->label('Severity')
                    ->colors([
                        'success' => 'minor',
                        'warning' => 'moderate',
                        'danger' => 'serious',
                        'primary' => fn ($state) => $state === 'critical',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'minor',
                        'heroicon-o-exclamation-circle' => 'moderate',
                        'heroicon-o-exclamation-triangle' => 'serious',
                        'heroicon-o-shield-exclamation' => 'critical',
                    ]),
                Tables\Columns\IconColumn::make('parent_notified')
                    ->label('Parent Notified')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('resolved')
                    ->label('Resolved')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'behavioral' => 'Behavioral',
                        'academic' => 'Academic',
                        'safety' => 'Safety',
                        'health' => 'Health',
                        'bullying' => 'Bullying',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'minor' => 'Minor',
                        'moderate' => 'Moderate',
                        'serious' => 'Serious',
                        'critical' => 'Critical',
                    ]),
                Tables\Filters\SelectFilter::make('student')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('parent_notified')
                    ->label('Parent Notified')
                    ->placeholder('All incidents')
                    ->trueLabel('Notified')
                    ->falseLabel('Not Notified'),
                Tables\Filters\TernaryFilter::make('resolved')
                    ->label('Resolved')
                    ->placeholder('All incidents')
                    ->trueLabel('Resolved')
                    ->falseLabel('Unresolved'),
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
                                fn (Builder $query, $date): Builder => $query->whereDate('incident_date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('incident_date', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('export_all_csv')
                        ->label('Export as CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function () {
                            $records = IncidentLog::with('student')
                                ->where('teacher_id', Auth::id())
                                ->orderBy('incident_date', 'desc')
                                ->get();

                            $filename = 'incidents-' . now()->format('Ymd-His') . '.csv';

                            return response()->streamDownload(function () use ($records) {
                                $handle = fopen('php://output', 'w');
                                fputcsv($handle, ['Date', 'Time', 'Student', 'Type', 'Severity', 'Title', 'Parent Notified', 'Resolved']);
                                foreach ($records as $r) {
                                    fputcsv($handle, [
                                        optional($r->incident_date)->format('Y-m-d') ?? (string) $r->incident_date,
                                        $r->incident_time,
                                        optional($r->student)->name,
                                        $r->type,
                                        $r->severity,
                                        $r->title,
                                        $r->parent_notified ? 'Yes' : 'No',
                                        $r->resolved ? 'Yes' : 'No',
                                    ]);
                                }
                                fclose($handle);
                            }, $filename, [
                                'Content-Type' => 'text/csv',
                            ]);
                        }),
                    Tables\Actions\Action::make('export_all_pdf')
                        ->label('Export as PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function () {
                            $records = IncidentLog::with('student')
                                ->where('teacher_id', Auth::id())
                                ->orderBy('incident_date', 'desc')
                                ->get();

                            $pdf = Pdf::loadView('exports.incidents', [
                                'records' => $records,
                            ])->setPaper('a4', 'portrait');

                            $filename = 'incidents-' . now()->format('Ymd-His') . '.pdf';
                            return response()->streamDownload(fn () => print($pdf->output()), $filename, [
                                'Content-Type' => 'application/pdf',
                            ]);
                        }),
                ])
                    ->label('Export Incidents')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->button(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_selected_csv')
                        ->label('Export Selected CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            $filename = 'incidents-selected-' . now()->format('Ymd-His') . '.csv';
                            return response()->streamDownload(function () use ($records) {
                                $handle = fopen('php://output', 'w');
                                fputcsv($handle, ['Date', 'Time', 'Student', 'Type', 'Severity', 'Title', 'Parent Notified', 'Resolved']);
                                foreach ($records as $r) {
                                    $r->loadMissing('student');
                                    fputcsv($handle, [
                                        optional($r->incident_date)->format('Y-m-d') ?? (string) $r->incident_date,
                                        $r->incident_time,
                                        optional($r->student)->name,
                                        $r->type,
                                        $r->severity,
                                        $r->title,
                                        $r->parent_notified ? 'Yes' : 'No',
                                        $r->resolved ? 'Yes' : 'No',
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
                            $pdf = Pdf::loadView('exports.incidents', [
                                'records' => $records,
                            ])->setPaper('a4', 'portrait');
                            $filename = 'incidents-selected-' . now()->format('Ymd-His') . '.pdf';
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
            'index' => Pages\ListIncidentLogs::route('/'),
            'create' => Pages\CreateIncidentLog::route('/create'),
            'edit' => Pages\EditIncidentLog::route('/{record}/edit'),
        ];
    }
}
