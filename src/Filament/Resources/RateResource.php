<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Filament\Resources\RateResource\Pages;
use ApproTickets\Filament\Resources\RateResource\RelationManagers;
use ApproTickets\Models\Rate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Concerns\Translatable;

class RateResource extends Resource
{
    use Translatable;
    protected static ?string $model = Rate::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';
    protected static ?string $modelLabel = 'tarifa';
    protected static ?string $pluralModelLabel = 'tarifes';
    protected static ?string $navigationGroup = 'Entrades';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Nom')
                    ->required()
                    ->columnSpan('full'),
                Textarea::make('description')
                    ->label('Descripció')
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Tarifa')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('description')->label('Descripció')->sortable()->words(10),
                Tables\Columns\TextColumn::make('product_count')->counts('product')->badge()->sortable()
                    ->badge()->label('Productes')

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
            'index' => Pages\ListRates::route('/'),
            'create' => Pages\CreateRate::route('/create'),
            'edit' => Pages\EditRate::route('/{record}/edit'),
        ];
    }
}
