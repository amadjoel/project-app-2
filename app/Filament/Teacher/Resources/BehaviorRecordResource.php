<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\BehaviorRecordResource\Pages;
use App\Filament\Teacher\Resources\BehaviorRecordResource\RelationManagers;
use App\Models\BehaviorRecord;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class BehaviorRecordResource extends Resource
{
    protected static ?string $model = BehaviorRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    
    protected static ?string $navigationLabel = 'Behavior Monitoring';
    
    protected static ?string $modelLabel = 'Behavior Record';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Behavior Information')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Student')
                            ->relationship('student', 'name', function ($query) {
                                $teacherId = Auth::id();
                                return $query->whereHas('roles', function ($q) {
                                    $q->where('name', 'student');
                                })->whereIn('id', function ($subQuery) use ($teacherId) {
                                    $subQuery->select('student_id')
                                        ->from('attendances')
                                        ->where('teacher_id', $teacherId)
                                        ->distinct();
                                });
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\DatePicker::make('date')
                            ->default(now())
                            ->required()
                            ->native(false),
                        
                        Forms\Components\TimePicker::make('time')
                            ->default(now())
                            ->seconds(false)
                            ->required()
                            ->native(false),
                        
                        Forms\Components\Select::make('type')
                            ->options([
                                'positive' => 'Positive',
                                'negative' => 'Negative',
                                'neutral' => 'Neutral',
                            ])
                            ->default('neutral')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Auto-set points based on type
                                if ($state === 'positive') {
                                    $set('points', 1);
                                } elseif ($state === 'negative') {
                                    $set('points', -1);
                                } else {
                                    $set('points', 0);
                                }
                            }),
                        
                        Forms\Components\Select::make('category')
                            ->options([
                                'participation' => 'Participation',
                                'cooperation' => 'Cooperation',
                                'respect' => 'Respect',
                                'responsibility' => 'Responsibility',
                                'leadership' => 'Leadership',
                                'conflict' => 'Conflict',
                                'disruption' => 'Disruption',
                                'rule_violation' => 'Rule Violation',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Brief description of behavior'),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Detailed description of what happened...'),
                        
                        Forms\Components\TextInput::make('points')
                            ->numeric()
                            ->default(0)
                            ->helperText('Positive points for good behavior, negative for issues')
                            ->required(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Parent Communication')
                    ->schema([
                        Forms\Components\Toggle::make('parent_notified')
                            ->label('Parent Notified')
                            ->default(false)
                            ->live(),
                        
                        Forms\Components\DateTimePicker::make('parent_notified_at')
                            ->label('Notification Date/Time')
                            ->visible(fn (Forms\Get $get) => $get('parent_notified'))
                            ->native(false),
                        
                        Forms\Components\Textarea::make('parent_response')
                            ->label('Parent Response/Feedback')
                            ->visible(fn (Forms\Get $get) => $get('parent_notified'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Follow-up')
                    ->schema([
                        Forms\Components\Toggle::make('requires_followup')
                            ->label('Requires Follow-up')
                            ->default(false)
                            ->live(),
                        
                        Forms\Components\Textarea::make('followup_notes')
                            ->label('Follow-up Notes')
                            ->visible(fn (Forms\Get $get) => $get('requires_followup'))
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\DateTimePicker::make('followup_completed_at')
                            ->label('Follow-up Completed At')
                            ->visible(fn (Forms\Get $get) => $get('requires_followup'))
                            ->native(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('time')
                    ->time('H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('student.name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'positive',
                        'danger' => 'negative',
                        'gray' => 'neutral',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                
                Tables\Columns\BadgeColumn::make('category')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'participation' => 'Participation',
                        'cooperation' => 'Cooperation',
                        'respect' => 'Respect',
                        'responsibility' => 'Responsibility',
                        'leadership' => 'Leadership',
                        'conflict' => 'Conflict',
                        'disruption' => 'Disruption',
                        'rule_violation' => 'Rule Violation',
                        'other' => 'Other',
                        default => $state,
                    })
                    ->colors([
                        'success' => fn ($state) => in_array($state, ['participation', 'cooperation', 'respect', 'responsibility', 'leadership']),
                        'danger' => fn ($state) => in_array($state, ['conflict', 'disruption', 'rule_violation']),
                        'gray' => 'other',
                    ]),
                
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30)
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('points')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray'))
                    ->formatStateUsing(fn ($state) => $state > 0 ? "+{$state}" : $state),
                
                Tables\Columns\IconColumn::make('parent_notified')
                    ->boolean()
                    ->label('Parent Notified')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('requires_followup')
                    ->boolean()
                    ->label('Follow-up')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'positive' => 'Positive',
                        'negative' => 'Negative',
                        'neutral' => 'Neutral',
                    ]),
                
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'participation' => 'Participation',
                        'cooperation' => 'Cooperation',
                        'respect' => 'Respect',
                        'responsibility' => 'Responsibility',
                        'leadership' => 'Leadership',
                        'conflict' => 'Conflict',
                        'disruption' => 'Disruption',
                        'rule_violation' => 'Rule Violation',
                        'other' => 'Other',
                    ]),
                
                Tables\Filters\SelectFilter::make('student')
                    ->relationship('student', 'name', function ($query) {
                        $teacherId = Auth::id();
                        return $query->whereHas('roles', function ($q) {
                            $q->where('name', 'student');
                        })->whereIn('id', function ($subQuery) use ($teacherId) {
                            $subQuery->select('student_id')
                                ->from('attendances')
                                ->where('teacher_id', $teacherId)
                                ->distinct();
                        });
                    })
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\TernaryFilter::make('parent_notified')
                    ->label('Parent Notified'),
                
                Tables\Filters\TernaryFilter::make('requires_followup')
                    ->label('Requires Follow-up'),
                
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->native(false),
                        Forms\Components\DatePicker::make('until')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('mark_parent_notified')
                        ->label('Mark Parent Notified')
                        ->icon('heroicon-o-bell')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update([
                                'parent_notified' => true,
                                'parent_notified_at' => now(),
                            ]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('mark_followup_complete')
                        ->label('Mark Follow-up Complete')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update([
                                'followup_completed_at' => now(),
                            ]);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('teacher_id', Auth::id())
            ->with(['student', 'teacher']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBehaviorRecords::route('/'),
            'create' => Pages\CreateBehaviorRecord::route('/create'),
            'edit' => Pages\EditBehaviorRecord::route('/{record}/edit'),
        ];
    }
}
