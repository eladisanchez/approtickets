<?php

namespace ApproTickets\Filament\Resources\ExtractResource\Pages;

use ApproTickets\Filament\Resources\ExtractResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use ApproTickets\Models\Booking;

class ListExtracts extends ListRecords
{
    protected static string $resource = ExtractResource::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->addSelect([
            'total' => Booking::query()
                // Seleccionem la suma, igual que feies a l'accessor
                ->select(DB::raw('SUM(tickets * price)'))

                // Condicions de la comanda (order)
                ->whereHas('order', function ($q) {
                    $q->where('payment', 'card')->where('paid', 1);
                })

                // Vinculem la data de la subconsulta amb la data de l'Extract principal
                ->whereColumn('created_at', '>=', 'extracts.date_start')
                ->whereColumn('created_at', '<=', 'extracts.date_end')

                // Aquesta és la part clau. Vinculem els bookings amb el product_id o user_id de l'Extract
                ->where(function (Builder $query) {
                    $query->whereHas('product', function (Builder $q) {
                        // Si l'Extract té product_id, filtrem per aquest
                        $q->whereColumn('products.id', 'extracts.product_id');
                    })->orWhereHas('product', function (Builder $q) {
                        // Si no, filtrem per l'user_id de l'Extract (i ens assegurem que product_id és null a l'Extract)
                        $q->whereNull('extracts.product_id')
                            ->whereColumn('products.user_id', 'extracts.user_id');
                    });
                })
                ->whereNull('refund')
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
