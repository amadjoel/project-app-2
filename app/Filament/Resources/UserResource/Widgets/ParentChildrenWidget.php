<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ParentChildrenWidget extends BaseWidget
{
    public $record = null;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Children / Students';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->role('student')
                    ->whereHas('parents', fn($query) => $query->where('parent_id', $this->record?->id))
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
                Tables\Columns\TextColumn::make('class.name')
                    ->label('Class')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('Date of Birth')
                    ->date('M d, Y')
                    ->sortable(),
            ])
            ->emptyStateHeading('No children assigned')
            ->emptyStateDescription('This parent has no children/students assigned yet.');
    }
}
