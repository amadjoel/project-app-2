<?php

namespace App\Filament\Parent\Resources;

use App\Filament\Parent\Resources\BehaviorRecordResource\Pages;
use App\Filament\Parent\Resources\BehaviorRecordResource\RelationManagers;
use App\Models\BehaviorRecord;
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
    
    protected static ?string $navigationLabel = 'Behavior';
    
    protected static ?int $navigationSort = 3;

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
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('time')
                    ->time('H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Child')
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
                    ->limit(40)
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('points')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray'))
                    ->formatStateUsing(fn ($state) => $state > 0 ? "+{$state}" : $state),
                
                Tables\Columns\IconColumn::make('parent_notified')
                    ->label('Notified')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Teacher')
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
            ])
            ->bulkActions([
                // No bulk actions for parents
            ])
            ->defaultSort('date', 'desc');
    }
    
    public static function getEloquentQuery(): Builder
    {
        // Only show behavior records for current parent's children
        return parent::getEloquentQuery()
            ->whereIn('student_id', Auth::user()->students->pluck('id'))
            ->with(['student', 'teacher']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBehaviorRecords::route('/'),
            'view' => Pages\ViewBehaviorRecord::route('/{record}'),
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
