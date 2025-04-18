<?php

namespace ApproTickets\Filament\Resources\TicketResource\Pages;

use ApproTickets\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('En venda')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('day', '>=', date('Y-m-d'))),
            'previous' => Tab::make('Anteriors')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('day', '<', date('Y-m-d'))->orderBy('day', 'desc')),
        ];
    }
}
