<?php

namespace ApproTickets\Filament\Resources\OptionResource\Pages;

use ApproTickets\Filament\Resources\OptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOption extends EditRecord
{
    protected static string $resource = OptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }
}
