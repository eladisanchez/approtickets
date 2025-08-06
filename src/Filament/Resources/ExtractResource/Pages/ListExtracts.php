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
        $query = parent::getTableQuery()->addSelect([
            'total' => Booking::query()
                ->select(DB::raw('SUM(tickets * price)'))
                ->whereHas('order', fn($q) => $q->where('payment', 'card')->where('paid', 1))
                ->whereRaw('DATE(created_at) >= extracts.date_start')
                ->whereRaw('DATE(created_at) <= extracts.date_end')
                ->where(function (Builder $query) {
                    $query
                        ->whereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('products')
                                ->whereColumn('products.id', 'bookings.product_id')
                                ->whereColumn('products.id', 'extracts.product_id');
                        })
                        ->orWhereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('products')
                                ->whereColumn('products.id', 'bookings.product_id')
                                ->whereNull('extracts.product_id')
                                ->whereColumn('products.user_id', 'extracts.user_id');
                        });
                })
                ->whereNull('refund'),
        ]);
        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
