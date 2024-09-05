<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Filament\Resources\OrderResource\Pages;
use ApproTickets\Filament\Resources\OrderResource\RelationManagers;
use ApproTickets\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Comandes';
    protected static ?string $modelLabel = 'comanda';
    protected static ?string $pluralModelLabel = 'comandes';
    protected static ?string $navigationGroup = 'Vendes';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\DateTimePicker::make('created_at')->label('Data')->disabled()->columnSpan(3),
                Forms\Components\TextInput::make('total')->label('Total')->disabled()->columnSpan(3),
                Forms\Components\TextInput::make('name')->label('Client')->columnSpan(2),
                Forms\Components\TextInput::make('email')->label('Correu electrònic')->columnSpan(2),
                Forms\Components\TextInput::make('phone')->label('Telèfon')->columnSpan(2),
                Forms\Components\Select::make('payment')->label('Mètode de pagament')
                    ->options(config('approtickets.payment_methods'))->required()->columnSpan(2),
                Forms\Components\Select::make('paid')->label('Estat pagament')->options([
                    '0' => 'Pendent',
                    '1' => 'Pagat',
                    '2' => 'Cancel·lat',
                ])->required()->columnSpan(2),
                Forms\Components\TextInput::make('tpv_id')->label('Resposta TPV')->disabled()->columnSpan(2),
                Forms\Components\Grid::make([
                    Forms\Components\TextInput::make('bookings')->label('Productes')->disabled()->columnSpan(6),
                ])
            ])->columns(6);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable()->description(fn(Order $record): string => substr($record->tpv_id, -3))
                    ->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('created_at')->date()->label('Data')->sortable()->date('d/m/y H:i:s'),
                // Tables\Columns\IconColumn::make('user')->label('')->icon(fn(string $state): string => $state ?
                //     'heroicon-o-user' : null)->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Client')->sortable()->searchable()->description(fn(Order $record): string => $record->email)->limit(30)->wrap(),
                Tables\Columns\TextColumn::make('bookings.product.title')->listWithLineBreaks()->label('Productes')->badge(),
                Tables\Columns\TextColumn::make('total')->label('Total')->suffix(' €'),
                Tables\Columns\IconColumn::make('paid')->label('Pagat')
                    ->icon(fn(string $state): string => match ($state) {
                        '0' => 'heroicon-o-clock',
                        '1' => 'heroicon-o-check',
                        '2' => 'heroicon-o-x-mark',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        '0' => 'warning',
                        '1' => 'success',
                        '2' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('payment')->label('Mètode'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document')
                    ->url(function ($record) {
                        return route('order.pdf', [
                            'id' => $record->id,
                            'session' => $record->session
                        ]);
                    })
                    ->openUrlInNewTab(),
                Tables\Actions\RestoreAction::make()
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
            'bookings' => RelationManagers\BookingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

}
