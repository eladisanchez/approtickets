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
use ApproTickets\Filament\Exports\BookingExporter;
use Filament\Tables\Actions\ExportAction;

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
                Tables\Columns\TextColumn::make('created_at')->label('Data compra')->sortable()->searchable()->date('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('order.email')->label('Client')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('product.title')->label('Producte')->sortable()->searchable()->wrap(),
                Tables\Columns\TextColumn::make('rate.title')->label('Tarifa')->sortable(),
                Tables\Columns\TextColumn::make('tickets')->label('Qt.')->sortable(),
                Tables\Columns\TextColumn::make('formattedSession')->label('Sessió')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('reducedSeat')->label('Localitat')->sortable(),
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
            ->headerActions([
                ExportAction::make()
                    ->exporter(BookingExporter::class)
            ])
            ->filters([
                // Filter by day with datepicker
                Tables\Filters\Filter::make('day')
                    ->form([
                        Forms\Components\DatePicker::make('day')->label('Dia'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $data['day'] ? $query->where('day', $data['day']) : $query),
                // Filter by product
                Tables\Filters\Filter::make('product')
                    ->form([
                        Forms\Components\Select::make('product')
                            ->label('Producte')
                            ->relationship('product', 'title'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $data['product'] ? $query->where('product_id', $data['product']) : $query),
                // Filter by if has scans
                Tables\Filters\Filter::make('scans')
                    ->form([
                        Forms\Components\Toggle::make('scans')->label('Escanejat'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $data['scans'] ? $query->whereHas('scans') : $query),

            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->orderBy('created_at', 'DESC'));
    }

    public static function getEloquentQuery(): Builder
    {
        if (!auth()->user()->hasRole('admin')) {
            $products = auth()->user()->products()->pluck('id');
            return parent::getEloquentQuery()->whereIn('product_id', $products);
        }
        return parent::getEloquentQuery();
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
