<?php

namespace ApproTickets\Filament\Resources;

use ApproTickets\Filament\Resources\RefundResource\Pages;
use ApproTickets\Models\Refund;
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
use Log;

class RefundResource extends Resource
{

    protected static ?string $model = Refund::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?string $navigationLabel = 'Devolucions';
    protected static ?string $modelLabel = 'devolució';
    protected static ?string $pluralModelLabel = 'devolucions';
    protected static ?string $navigationGroup = 'Vendes';
    protected static ?int $navigationSort = 8;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->date('d/m/Y H:i')->label('Creada'),
                Tables\Columns\TextColumn::make('total')->numeric()->suffix(' €'),
                Tables\Columns\TextColumn::make('product.title')->badge()->sortable()->searchable(isIndividual: true, isGlobal: true)
                    ->label('Producte'),
                Tables\Columns\TextColumn::make('order.id')->sortable()->searchable(isIndividual: true, isGlobal: true)
                    ->label('Comanda')
                    ->badge()
                    ->url(function ($record) {
                        if (!$record->order_id) {
                            return null;
                        }
                        return route('filament.admin.resources.orders.edit', [
                            'record' => $record->order_id,
                        ]);
                    }),
                Tables\Columns\TextColumn::make('session_canceled')->date('d/m/Y H:i')->label('Sessió cancel·lada'),
                Tables\Columns\TextColumn::make('session_new')->date('d/m/Y H:i')->label('Nova sessió'),
                Tables\Columns\TextColumn::make('refunded_at')->label('Efectuada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-m-check')
                    ->iconColor('success')
                    ->placeholder('No'),
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
            ])
            ->actions([
                Tables\Actions\Action::make('url')
                    ->label('Enllaç')
                    ->icon('heroicon-o-link')
                    ->url(function (Refund $record) {
                        if (!$record->hash) {
                            return null;
                        }
                        return route('refund', [
                            'hash' => $record->hash
                        ]);
                    })
                    ->openUrlInNewTab()
                    ->hidden(function ($record) {
                        return $record->refunded_at;
                    }),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->orderBy('created_at', 'DESC'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRefunds::route('/'),
        ];
    }

}
