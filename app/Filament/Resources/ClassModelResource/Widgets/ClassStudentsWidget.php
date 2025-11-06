<?php

namespace App\Filament\Resources\ClassModelResource\Widgets;

use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ClassStudentsWidget extends BaseWidget
{
    public $record = null;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Students in This Class';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->role('student')
                    ->where('class_id', $this->record?->id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable()
                    ->url(fn (User $record): string => route('filament.admin.resources.users.edit', ['record' => $record]))
                    ->color('primary'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('Date of Birth')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('guardian.name')
                    ->label('Guardian')
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('remove')
                    ->label('Remove from Class')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update(['class_id' => null]);
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('assign')
                    ->label('Assign Student')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Forms\Components\Select::make('student_id')
                            ->label('Select Student')
                            ->options(fn() => User::role('student')->whereNull('class_id')->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (array $data) {
                        User::find($data['student_id'])->update(['class_id' => $this->record->id]);
                    }),
            ]);
    }
}
