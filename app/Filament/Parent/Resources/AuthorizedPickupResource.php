<?php

namespace App\Filament\Parent\Resources;

use App\Filament\Parent\Resources\AuthorizedPickupResource\Pages;
use App\Filament\Parent\Resources\AuthorizedPickupResource\RelationManagers;
use App\Models\AuthorizedPickup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class AuthorizedPickupResource extends Resource
{
    protected static ?string $model = AuthorizedPickup::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Authorized Pickups';
    
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label('Child')
                    ->relationship('student', 'name', function ($query) {
                        return $query->whereIn('id', Auth::user()->students->pluck('id'));
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Forms\Components\Select::make('rfid_card_id')
                    ->label('RFID Card')
                    ->relationship('rfidCard', 'card_number')
                    ->searchable()
                    ->preload()
                    ->helperText('Select the RFID card authorized to pick up this child'),
                
                    Forms\Components\TimePicker::make('time_in')
                        ->label('Time In')
                        ->seconds(false)
                        ->native(false)
                        ->helperText('Actual time when the authorized person arrived at school to pick up the child'),
                
                    Forms\Components\TimePicker::make('time_out')
                        ->label('Time Out')
                        ->seconds(false)
                        ->native(false)
                        ->helperText('Actual time when they left the school with the child'),
                
                Forms\Components\Toggle::make('allowed')
                    ->label('Authorized')
                    ->default(true)
                    ->required(),
                
                Forms\Components\Textarea::make('notes')
                    ->rows(3)
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->placeholder('Additional notes about this pickup authorization...'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Child')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('rfidCard.card_number')
                    ->label('RFID Card')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('rfidCard.user.name')
                    ->label('Authorized Person')
                    ->searchable()
                    ->sortable(),
                
                    Tables\Columns\TextColumn::make('time_in')
                        ->label('Time In')
                        ->time('H:i')
                        ->sortable()
                        ->placeholder('—'),
                
                    Tables\Columns\TextColumn::make('time_out')
                        ->label('Time Out')
                        ->time('H:i')
                        ->sortable()
                        ->placeholder('—'),
                
                Tables\Columns\IconColumn::make('allowed')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('notes')
                    ->limit(40)
                    ->wrap()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('student')
                    ->relationship('student', 'name', function ($query) {
                        return $query->whereIn('id', Auth::user()->students->pluck('id'));
                    })
                    ->preload()
                    ->label('Child'),
                
                Tables\Filters\TernaryFilter::make('allowed')
                    ->label('Authorization Status'),
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
    
    public static function getEloquentQuery(): Builder
    {
        // Only show authorized pickups for current parent's children
        return parent::getEloquentQuery()
            ->where('parent_id', Auth::id())
            ->with(['student', 'rfidCard.user']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthorizedPickups::route('/'),
            'create' => Pages\CreateAuthorizedPickup::route('/create'),
            'edit' => Pages\EditAuthorizedPickup::route('/{record}/edit'),
        ];
    }
}
