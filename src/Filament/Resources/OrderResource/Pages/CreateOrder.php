<?php

namespace ApproTickets\Filament\Resources\OrderResource\Pages;

use ApproTickets\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
