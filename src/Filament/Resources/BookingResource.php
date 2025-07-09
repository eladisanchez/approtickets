<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Filament\Resources\BookingResource\Pages;
use ApproTickets\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use ApproTickets\Filament\Exports\BookingExporter;
use Filament\Tables\Actions\ExportAction;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Entrades venudes';
    protected static ?string $modelLabel = 'entrada';
    protected static ?string $pluralModelLabel = 'entrades';
    protected static ?string $navigationGroup = 'Vendes';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('created_at')->label('Data')->disabled()->columnSpan(2),
                // Forms\Components\Placeholder::make('order')->label('Client')->content(fn($record): string => $record?->order?->email ?? '')->columnSpan(2),
                Forms\Components\Select::make('product')->relationship(name: 'product', titleAttribute: 'title')->label('Producte')->disabled()->columnSpan(2),
                Forms\Components\TextInput::make('tickets')->label('Quantitat')->columnSpan(2),
                Forms\Components\DatePicker::make('day')->label('Dia')->required()->columnSpan(2),
                Forms\Components\TimePicker::make('hour')->label('Hora')->required()->columnSpan(2),
                Forms\Components\TextInput::make('row')->numeric()->label('Fila')->columnSpan(1)->visible(fn($record): bool => !!$record?->product?->venue_id),
                Forms\Components\TextInput::make('seat')->numeric()->label('Seient')->columnSpan(1)->visible(fn($record): bool => !!$record?->product?->venue_id),
            ])->columns(6);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('order.paid')->label('Estat')->default(3),
                Tables\Columns\TextColumn::make('created_at')->label('Data compra')->sortable()->searchable()->date('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('order.email')->label('Client')->sortable()->searchable(isIndividual: true, isGlobal: true),
                Tables\Columns\TextColumn::make('product.title')->label('Producte')->sortable()->searchable(isIndividual: true, isGlobal: true)->wrap(),
                Tables\Columns\TextColumn::make('rate.title')->label('Tarifa')->sortable(),
                Tables\Columns\TextColumn::make('tickets')->label('Qt.')->sortable(),
                Tables\Columns\TextColumn::make('day')
                    ->label('Sessió')
                    ->sortable()
                    ->formatStateUsing(fn(Booking $record): string => $record->formattedSession),
                Tables\Columns\TextColumn::make('reducedSeat')->label('Localitat')->sortable(),
                Tables\Columns\TextColumn::make('scans_count')->counts('scans')->label('QR')->badge()->color('success')->tooltip(fn(Booking $record): string => $record->scans->pluck('scan_id')->implode(', ')),
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
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_at')->label('Data compra'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $data['created_at'] ? $query->whereDate('created_at', $data['created_at']) : $query),
                // Filter by day with datepicker
                Tables\Filters\Filter::make('session')
                    ->form([
                        Forms\Components\DatePicker::make('day')->label('Sessió'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $data['day'] ? $query->where('day', $data['day']) : $query),
                // Filter by product
                Tables\Filters\Filter::make('product')
                    ->form([
                        Forms\Components\Select::make('product')
                            ->label('Producte')
                            ->relationship('product', 'title')
                            ->searchable(),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $data['product'] ? $query->where('product_id', $data['product']) : $query),
                // Filter by if has scans
                Tables\Filters\Filter::make('scans')
                    ->form([
                        Forms\Components\Toggle::make('scans')->label('Escanejat'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $data['scans'] ? $query->whereHas('scans') : $query),
                // Filter by order.paid
                Tables\Filters\Filter::make('paid_status')
                    ->form([
                        Forms\Components\Select::make('paid_status')
                            ->label('Estat')
                            ->options([
                                'cart' => 'Cistell',
                                '0' => 'Pendent',
                                '1' => 'Comanda completada',
                            ])
                    ])
                    ->query(
                        function (Builder $query, array $data) {
                            if (isset($data['paid_status'])) {
                                if ($data['paid_status'] == 'cart') {
                                    return $query->whereDoesntHave('order');
                                }
                                return $query->whereHas('order', fn($q) => $q->where('paid', $data['paid_status']));
                            }
                            return $query;
                        }
                    ),

                ])
                ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        if (!auth()->user()->hasRole('admin')) {
            $products = auth()->user()->products()->pluck('id');
            return parent::getEloquentQuery()->whereIn('product_id', $products)->with('order');
        }
        return parent::getEloquentQuery()->with('order');
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
