<?php

namespace ApproTickets\Filament\Resources\CategoryResource\Pages;

use ApproTickets\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;


class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Categories amb activitats')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('products')),
            'inactive' => Tab::make('Categories no utilitzades')
                ->modifyQueryUsing(fn(Builder $query) => $query->doesntHave('products')),
        ];
    }
}
