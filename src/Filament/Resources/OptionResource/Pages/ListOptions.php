<?php

namespace ApproTickets\Filament\Resources\OptionResource\Pages;

use ApproTickets\Filament\Resources\OptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOptions extends ListRecords
{
    protected static string $resource = OptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->visible(auth()->user()->isSuperadmin()),
        ];
    }
}
