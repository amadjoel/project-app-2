<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthorizedPickupResource\Pages;
use App\Models\AuthorizedPickup;
use App\Models\RFIDCard;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AuthorizedPickupResource extends Resource
{
    protected static ?string $model = AuthorizedPickup::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?int $navigationSort = 3;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Select::make('rfid_card_id')
                        ->label('RFID Card')
                        ->options(function () {
                            return RFIDCard::whereHas('user', function ($q) {
                                $q->role('parent');
                            })
                            ->where('status', 'active')
                            ->with('user')
                            ->get()
                            ->mapWithKeys(function ($card) {
                                return [$card->id => $card->card_number . ' - ' . $card->user->name];
                            });
                        })
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $card = RFIDCard::find($state);
                                if ($card && $card->user_id) {
                                    $set('parent_id', $card->user_id);
                                }
                            }
                        }),
                    Forms\Components\Select::make('parent_id')
                        ->label('Parent')
                        ->options(User::role('parent')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->disabled()
                        ->dehydrated(),
                    Forms\Components\Select::make('student_id')
                        ->label('Student')
                        ->options(User::role('student')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Forms\Components\Toggle::make('allowed')
                        ->label('Allowed to pick up')
                        ->default(true),
                    Forms\Components\Textarea::make('notes')
                        ->nullable()
                        ->columnSpanFull(),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('parent.name')->label('Parent')->sortable()->searchable(),
            TextColumn::make('student.name')->label('Student')->sortable()->searchable(),
            TextColumn::make('rfidCard.card_number')->label('RFID Card')->sortable()->searchable(),
            BooleanColumn::make('allowed')->label('Allowed')->sortable(),
            TextColumn::make('notes')->limit(50)->wrap(),
            TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
        ])
        ->filters([
            Tables\Filters\TrashedFilter::make(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
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
