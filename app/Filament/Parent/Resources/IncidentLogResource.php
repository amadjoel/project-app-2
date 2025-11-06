<?php

namespace App\Filament\Parent\Resources;

use App\Filament\Parent\Resources\IncidentLogResource\Pages;
use App\Filament\Parent\Resources\IncidentLogResource\RelationManagers;
use App\Models\IncidentLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class IncidentLogResource extends Resource
{
    protected static ?string $model = IncidentLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';
    
    protected static ?string $navigationLabel = 'Incidents';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('incident_date')
                    ->label('Date')
                    ->date()
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('incident_time')
                    ->label('Time')
                    ->time('H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Child')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'danger' => 'behavioral',
                        'warning' => 'academic',
                        'danger' => 'safety',
                        'info' => 'health',
                        'danger' => 'bullying',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                
                Tables\Columns\BadgeColumn::make('severity')
                    ->colors([
                        'danger' => 'critical',
                        'warning' => 'major',
                        'info' => 'minor',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40)
                    ->wrap(),
                
                Tables\Columns\IconColumn::make('parent_notified')
                    ->label('Notified')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('resolved')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Reported By')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('student')
                    ->relationship('student', 'name', function ($query) {
                        return $query->whereIn('id', Auth::user()->students->pluck('id'));
                    })
                    ->preload()
                    ->label('Child'),
                
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
                        'major' => 'Major',
                        'critical' => 'Critical',
                    ]),
                
                Tables\Filters\TernaryFilter::make('resolved')
                    ->label('Resolved'),
                
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
                                fn (Builder $query, $date): Builder => $query->whereDate('incident_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('incident_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions for parents
            ])
            ->defaultSort('incident_date', 'desc');
    }
    
    public static function getEloquentQuery(): Builder
    {
        // Only show incidents for current parent's children
        return parent::getEloquentQuery()
            ->whereIn('student_id', Auth::user()->students->pluck('id'))
            ->with(['student', 'teacher']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncidentLogs::route('/'),
            'view' => Pages\ViewIncidentLog::route('/{record}'),
        ];
    }
    
    public static function canCreate(): bool
    {
        return false;
    }
    
    public static function canEdit($record): bool
    {
        return false;
    }
    
    public static function canDelete($record): bool
    {
        return false;
    }
}
