<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Filament\Resources\TicketResource\Pages;
use ApproTickets\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Concerns\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class TicketResource extends Resource
{

    use Translatable;
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Sessions';
    protected static ?string $modelLabel = 'sessió';
    protected static ?string $pluralModelLabel = 'sessions';
    protected static ?string $navigationGroup = 'Entrades';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->label('Producte')
                    ->relationship('product', 'title')
                    ->columnSpanFull(),
                DatePicker::make('day')
                    ->label('Dia')
                    ->columnSpan(2),
                TimePicker::make('hour')
                    ->label('Hora')
                    ->columnSpan(2),
                TextInput::make('tickets')
                    ->label('Entrades')
                    ->numeric()
                    ->columnSpan(2),
            ])->columns(6);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.title')->badge()->sortable()->searchable(isIndividual: true, isGlobal: true)
                    ->label('Producte'),
                Tables\Columns\TextColumn::make('day')->date('d/m/Y')->label('Dia'),
                Tables\Columns\TextColumn::make('hour')->date('H:i')->label('Hora'),
                Tables\Columns\TextColumn::make('tickets')->label('Entrades'),
                Tables\Columns\TextColumn::make('available')->label('Disponibles')
            ])
            ->filters([
                Tables\Filters\Filter::make('product')
                    ->form([
                        Forms\Components\Select::make('product')
                            ->searchable()
                            ->label('Producte')
                            ->relationship('product', 'title'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $data['product'] ? $query->where('product_id', $data['product']) : $query),
                //Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar entrades')
                    ->modalDescription("Alerta! Les entrades ja adquirides per aquest dia i hora seguiran sent vàlides. Si vols cancel·lar totes les entrades, selecciona 'Cancelar sessió'."),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel·lar sessió')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel·lar sessió')
                    ->modalDescription("")
                    ->form([
                        Forms\Components\Toggle::make('refund')
                            ->label('Generar devolucions')
                            ->helperText("S'enviarà un email a tots els usuaris afectats amb un enllaç per poder efectuar la devolució."),
                        Forms\Components\DatePicker::make('new_date')
                            ->label('Nova data')
                            ->helperText("Especifica opcionalment un nou dia i hora de la sessió. Les entrades ja adquirides i no reemborsades seguiran sent vàlides pel nou horari."),

                    ])
                    ->action(function (Ticket $record, array $data) {
                        $record->cancel($data['new_date']);
                    })
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(auth()->user()->hasRole('admin')),
                Tables\Actions\Action::make('map')
                    ->label('Plànol')
                    ->url(fn(Ticket $record) => route('map', [
                        'product_id' => $record->product_id,
                        'day' => date('Y-m-d', strtotime($record->day)),
                        'hour' => date('H:i', strtotime($record->hour))
                    ]))
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->openUrlInNewTab()
                    ->visible(fn(Ticket $record) => $record->product && $record->product->venue_id),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(auth()->user()->hasRole('admin')),
                ]),
            ])
            ->defaultSort('day', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            //'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = static::getModel()::query();
        if (auth()->user()->hasRole('organizer')) {
            $query->whereHas('product', function (Builder $query) {
                $query->where('user_id', auth()->user()->id);
            });
        }
        return $query;
    }

}
