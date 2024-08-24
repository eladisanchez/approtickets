<?php

namespace ApproTickets\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Producte')
                    ->relationship('product', 'title')
                    ->required(),
                Forms\Components\TextInput::make('tickets')
                    ->label('Quantitat')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->label('Preu u.')
                    ->numeric()
                    ->suffix(' €')
                    ->required(),
                Forms\Components\Select::make('rate_id')
                    ->label('Tarifa')
                    ->relationship('rate', 'title')
                    ->required(),
                Forms\Components\DatePicker::make('day')
                    ->label('Dia')
                    ->required(),
                Forms\Components\TimePicker::make('hour')
                    ->label('Hora')
                    ->required(),
            ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.title')
            ->columns([
                Tables\Columns\TextColumn::make('product.title')
                    ->label('Producte'),
                Tables\Columns\TextColumn::make('rate.title')
                    ->label('Tarifa')
                    ->badge(),
                Tables\Columns\TextColumn::make('tickets')
                    ->label('Quantitat'),
                Tables\Columns\TextColumn::make('formattedSeat')
                    ->label('Localitat')
                    ->sortable(),
                Tables\Columns\TextColumn::make('day')
                    ->label('Dia')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('hour')
                    ->label('Hora')
                    ->date('H:i'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Preu')
                    ->suffix(' €')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Afegir producte a la comanda'),
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
}
