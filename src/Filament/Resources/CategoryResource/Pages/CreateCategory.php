<?php

namespace ApproTickets\Filament\Resources\CategoryResource\Pages;

use ApproTickets\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
