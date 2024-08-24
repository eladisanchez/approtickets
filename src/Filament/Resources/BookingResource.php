<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Filament\Resources\BookingResource\Pages;
use ApproTickets\Filament\Resources\BookingResource\RelationManagers;
use ApproTickets\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Entrades';
    protected static ?string $modelLabel = 'entrada';
    protected static ?string $pluralModelLabel = 'entrades';
    protected static ?string $navigationGroup = 'Vendes';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('created_at')->label('Data')->disabled()->columnSpan(2),
                Forms\Components\TextInput::make('order.email')->label('Client')->disabled()->columnSpan(2),
                Forms\Components\TextInput::make('product.title')->label('Producte')->disabled()->columnSpan(2),
                Forms\Components\TextInput::make('tickets')->label('Quantitat')->columnSpan(2),
                Forms\Components\DatePicker::make('day')->label('Dia')->required()->columnSpan(2),
                Forms\Components\TimePicker::make('hour')->label('Hora')->required()->columnSpan(2),
            ])->columns(6);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Data')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('order.email')->label('Client')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('product.title')->label('Producte')->sortable()->searchable()->wrap(),
                Tables\Columns\TextColumn::make('rate.title')->label('Tarifa')->sortable(),
                Tables\Columns\TextColumn::make('tickets')->label('Quantitat')->sortable(),
                Tables\Columns\TextColumn::make('formattedSeat')->label('Localitat')->sortable(),
                Tables\Columns\TextColumn::make('scans.scan_id')->label('QR')->badge()->color('success'),
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
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->orderBy('created_at', 'DESC'));
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
