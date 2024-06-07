<?php

namespace ApproTickets\Filament\Resources\OrderResource\Pages;

use ApproTickets\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Comandes confirmades'),
            'trashed' => Tab::make('Comandes eliminades')
                ->modifyQueryUsing(fn(Builder $query) => $query->onlyTrashed()),
        ];
    }
}
