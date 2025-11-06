<?php

namespace App\Filament\Resources\ClassModelResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guardian.name')
                    ->label('Guardian')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('assign')
                    ->form([
                        Forms\Components\Select::make('student_id')
                            ->label('Student')
                            ->options(fn() => User::role('student')->whereNull('class_id')->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (array $data, RelationManager $livewire): void {
                        User::find($data['student_id'])->update(['class_id' => $livewire->ownerRecord->id]);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('remove')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update(['class_id' => null]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('remove')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['class_id' => null]);
                        }),
                ]),
            ]);
    }
}
