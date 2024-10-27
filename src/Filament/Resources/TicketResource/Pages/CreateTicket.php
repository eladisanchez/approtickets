<?php

namespace ApproTickets\Filament\Resources\TicketResource\Pages;

use ApproTickets\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;
}
