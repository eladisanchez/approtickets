<?php

namespace ApproTickets\Filament\Resources\VenueResource\Pages;

use ApproTickets\Filament\Resources\VenueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditVenue extends EditRecord
{
    protected static string $resource = VenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            VenueResource\Widgets\LocationMap::make(),
        ];
    }
}
