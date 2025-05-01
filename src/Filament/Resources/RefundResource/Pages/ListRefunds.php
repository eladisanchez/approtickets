<?php

namespace ApproTickets\Filament\Resources\RefundResource\Pages;

use ApproTickets\Filament\Resources\RefundResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRefunds extends ListRecords
{
    protected static string $resource = RefundResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
