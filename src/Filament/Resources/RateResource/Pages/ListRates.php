<?php

namespace ApproTickets\Filament\Resources\RateResource\Pages;

use ApproTickets\Filament\Resources\RateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRates extends ListRecords
{
    protected static string $resource = RateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Tarifes vigents')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('productRates')),
            'inactive' => Tab::make('Tarifes no utilitzades')
                ->modifyQueryUsing(fn(Builder $query) => $query->doesntHave('productRates')),
        ];
    }
}
