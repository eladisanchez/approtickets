<?php

namespace ApproTickets\Filament\Exports;

use ApproTickets\Models\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use ApproTickets\Enums\PaymentStatus;
use ApproTickets\Enums\PaymentMethods;

class BookingExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('product.title')->label('Producte'),
            ExportColumn::make('rate.title')->label('Tarifa'),
            ExportColumn::make('row')->label('Fila'),
            ExportColumn::make('seat')->label('Seient'),
            ExportColumn::make('price')->label('Preu'),
            ExportColumn::make('day')->label('Dia')
                ->formatStateUsing(fn (string $state): string => date('d/m/Y', strtotime($state))),
            ExportColumn::make('hour')->label('Hora'),
            ExportColumn::make('tickets')->label('Quantitat'),
            ExportColumn::make('order.id')->label('ID comanda'),
            ExportColumn::make('order.name')->label('Nom'),
            ExportColumn::make('order.email')->label('Email'),
            ExportColumn::make('order.phone')->label('Telèfon'),
            ExportColumn::make('order.cp')->label('Codi postal'),
            ExportColumn::make('order.observations')->label('Observacions'),
            ExportColumn::make('order.payment')->label('Pagament')
                ->formatStateUsing(fn (PaymentMethods $state): string => $state->getLabel()),
            ExportColumn::make('order.paid')->label('Pagat')
                ->formatStateUsing(fn (PaymentStatus $state): string => $state->getLabel()),
            ExportColumn::make('total')->label('Total'),
            ExportColumn::make('created_at')->label('Data Compra'),
            //ExportColumn::make('updated_at')->label('Data actualització'),
            //ExportColumn::make('deleted_at')->label('Data esborrada'),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'ASC');
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your customer export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFileDisk(): string
    {
        return 'public';
    }

    public function getFileName(Export $export): string
    {
        return "orders-{$export->getKey()}";
    }

}
