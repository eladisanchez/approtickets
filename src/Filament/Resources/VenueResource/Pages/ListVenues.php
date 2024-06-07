<?php

namespace ApproTickets\Filament\Resources\VenueResource\Pages;

use ApproTickets\Filament\Resources\VenueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVenues extends ListRecords
{
    protected static string $resource = VenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
