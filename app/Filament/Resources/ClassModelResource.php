<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassModelResource\Pages;
use App\Filament\Resources\ClassModelResource\RelationManagers;
use App\Models\ClassModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassModelResource extends Resource
{
    protected static ?string $model = ClassModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Classes';

    protected static ?string $modelLabel = 'Class';

    protected static ?string $pluralModelLabel = 'Classes';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Class Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Class Name')
                            ->placeholder('e.g., Mathematics 101'),

                        Forms\Components\TextInput::make('grade_level')
                            ->maxLength(255)
                            ->label('Grade Level')
                            ->placeholder('e.g., Grade 10, Year 1'),

                        Forms\Components\TextInput::make('room_number')
                            ->maxLength(255)
                            ->label('Room Number')
                            ->placeholder('e.g., Room 205'),

                        Forms\Components\Select::make('teacher_id')
                            ->relationship('teacher', 'name', fn($query) => $query->role('teacher'))
                            ->searchable()
                            ->preload()
                            ->label('Assigned Teacher')
                            ->helperText('Each teacher can only be assigned to one class.')
                            ->nullable(),

                        Forms\Components\TextInput::make('capacity')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->label('Class Capacity')
                            ->placeholder('Maximum number of students'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active classes appear in student assignments.'),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Details')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->label('Description')
                            ->placeholder('Class description, schedule, or notes')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Class Name'),

                Tables\Columns\TextColumn::make('grade_level')
                    ->searchable()
                    ->sortable()
                    ->label('Grade Level')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('room_number')
                    ->searchable()
                    ->label('Room')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('teacher.name')
                    ->searchable()
                    ->sortable()
                    ->label('Teacher')
                    ->placeholder('Not assigned'),

                Tables\Columns\TextColumn::make('capacity')
                    ->numeric()
                    ->sortable()
                    ->label('Capacity')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('students_count')
                    ->counts('students')
                    ->label('Students')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All classes')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassModels::route('/'),
            'create' => Pages\CreateClassModel::route('/create'),
            'view' => Pages\ViewClassModel::route('/{record}'),
            'edit' => Pages\EditClassModel::route('/{record}/edit'),
        ];
    }
}
