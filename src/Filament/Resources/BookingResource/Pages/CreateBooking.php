<?php

namespace ApproTickets\Filament\Resources\BookingResource\Pages;

use ApproTickets\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;
}
