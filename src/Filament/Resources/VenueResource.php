<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Filament\Resources\VenueResource\Pages;
use ApproTickets\Models\Venue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Support\Enums\ActionSize;
use Filament\Forms\Components\Iframe;

class VenueResource extends Resource
{
    protected static ?string $model = Venue::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Espais';
    protected static ?string $modelLabel = 'espai';
    protected static ?string $pluralModelLabel = 'espais';
    protected static ?string $navigationGroup = 'Entrades';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nom de l\'espai')
                    ->required()
                    ->columnSpan('full'),
                TextInput::make('address')
                    ->label('Adreça')
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Espai'),
                Tables\Columns\TextColumn::make('seats')->label('Seients')
                ->getStateUsing(function ($record) {
                    return is_array($record->seats) ? count($record->seats) : 0;
                }),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->sortable()
                    ->label('Productes')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListVenues::route('/'),
            'create' => Pages\CreateVenue::route('/create'),
            'edit' => Pages\EditVenue::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            VenueResource\Widgets\LocationMap::class,
        ];
    }
}
