<?php

namespace ApproTickets\Filament\Resources\UserResource\Pages;

use ApproTickets\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
