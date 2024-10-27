<?php

namespace ApproTickets\Filament\Resources\TicketResource\Pages;

use ApproTickets\Filament\Resources\TicketResource;
use ApproTickets\Filament\Resources\VenueResource\Widgets\LocationMap;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        // Only show widget if seats is set
        if ($this->record->seats) {
            return [
                LocationMap::make(),
            ];
        }
        return [];
    }
}
