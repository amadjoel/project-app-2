<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RFIDCardResource\Pages;
use App\Filament\Resources\RFIDCardResource\RelationManagers;
use App\Models\RFIDCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RFIDCardResource extends Resource
{
    protected static ?string $model = RFIDCard::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('card_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'lost' => 'Lost',
                                'replaced' => 'Replaced',
                            ])
                            ->required()
                            ->default('active')
                            ->reactive(),
                        Forms\Components\Select::make('replaced_by_card_id')
                            ->relationship('replacementCard', 'card_number')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get) => $get('status') === 'replaced'),
                        Forms\Components\DateTimePicker::make('deactivated_at')
                            ->visible(fn (Forms\Get $get) => in_array($get('status'), ['inactive', 'lost', 'replaced'])),
                        Forms\Components\Textarea::make('deactivation_reason')
                            ->visible(fn (Forms\Get $get) => in_array($get('status'), ['inactive', 'lost', 'replaced']))
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('card_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'lost' => 'warning',
                        'replaced' => 'secondary',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('replacementCard.card_number')
                    ->label('Replaced By')
                    ->visible(fn ($record) => $record?->status === 'replaced'),
                Tables\Columns\TextColumn::make('deactivated_at')
                    ->dateTime()
                    ->sortable()
                    ->visible(fn ($record) => $record && in_array($record->status, ['inactive', 'lost', 'replaced'])),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'lost' => 'Lost',
                        'replaced' => 'Replaced',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_lost')
                    ->label('Mark Lost')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->action(function (RFIDCard $record) {
                        $record->update([
                            'status' => 'lost',
                            'deactivated_at' => now(),
                        ]);
                    })
                    ->visible(fn ($record) => $record?->status === 'active'),
                Tables\Actions\Action::make('reissue')
                    ->label('Reissue Card')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (RFIDCard $record, array $data) {
                        $newCard = RFIDCard::create([
                            'card_number' => $data['new_card_number'],
                            'user_id' => $record->user_id,
                            'status' => 'active',
                        ]);
                        
                        $record->update([
                            'status' => 'replaced',
                            'replaced_by_card_id' => $newCard->id,
                            'deactivated_at' => now(),
                            'deactivation_reason' => 'Card reissued',
                        ]);
                    })
                    ->form([
                        Forms\Components\TextInput::make('new_card_number')
                            ->label('New Card Number')
                            ->required()
                            ->unique('rfid_cards', 'card_number'),
                    ])
                    ->visible(fn ($record) => $record && in_array($record->status, ['active', 'lost'])),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRFIDCards::route('/'),
            'create' => Pages\CreateRFIDCard::route('/create'),
            'edit' => Pages\EditRFIDCard::route('/{record}/edit'),
        ];
    }
}
