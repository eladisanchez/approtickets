<?php

namespace ApproTickets\Filament\Resources\UserResource\Pages;

use ApproTickets\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'admin' => Tab::make('Administradors')
                ->modifyQueryUsing(fn(Builder $query) => $query->withRole('admin')),
            'entities' => Tab::make('Organitzadors')
                ->modifyQueryUsing(fn(Builder $query) => $query->withRole('organizer')),
            'validators' => Tab::make('Validadors')
                ->modifyQueryUsing(fn(Builder $query) => $query->withRole('validator')),
            'clients' => Tab::make('Tots')
                ->modifyQueryUsing(fn(Builder $query) => $query),
        ];
    }

}
