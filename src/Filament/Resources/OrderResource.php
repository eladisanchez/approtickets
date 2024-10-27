<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Enums\PaymentMethods;
use ApproTickets\Filament\Resources\OrderResource\Pages;
use ApproTickets\Filament\Resources\OrderResource\RelationManagers;
use ApproTickets\Models\Order;
use ApproTickets\Http\Controllers\RefundController;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;


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
                    ->options(PaymentMethods::class)->required()->columnSpan(2),
                Forms\Components\Select::make('paid')->label('Estat pagament')->options([
                    '0' => 'Pendent',
                    '1' => 'Pagat',
                    '2' => 'Cancel·lat',
                ])->required()->columnSpan(2),
                Forms\Components\TextInput::make('tpv_id')->label('ID TPV')->disabled()->columnSpan(2),
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
                Tables\Columns\TextColumn::make('payment')->badge()->label('Mètode'),

            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
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
                Tables\Actions\Action::make('refund')
                    ->label('Devolució')
                    ->icon('heroicon-o-arrow-left-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Devolució')
                    ->modalSubheading('')
                    ->modalContent(fn(Order $record) => new HtmlString("El total d'aquesta comanda és de {$record->total} €. Indica la quantitat a retornar. Pots fer una devolució parcial."))
                    ->form(function ($record) {
                        return [
                            Forms\Components\TextInput::make('amount')
                                ->label('Quantitat a retornar')
                                ->required()
                                ->numeric()
                                ->suffix(' €')
                                ->default($record->total)
                        ];
                    })
                    ->action(function (Order $record, array $data) {
                        $refund = $record->createRefund($data['amount']);
                        $refundRequest = RefundController::requestRefund($refund);
                        if ($refundRequest['error']) {
                            Notification::make()
                                ->title('Error en la petició de devolució')
                                ->body($refundRequest['error'])
                                ->danger()
                                ->send();
                        } else {
                            Notification::make()
                                ->title($refundRequest['message'])
                                ->success()
                                ->send();
                        }
                    }),
                Tables\Actions\RestoreAction::make()
                ])
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
            'refunds' => RelationManagers\RefundsRelationManager::class
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
