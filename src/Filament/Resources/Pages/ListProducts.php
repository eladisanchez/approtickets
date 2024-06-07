<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Productes actius')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('active', 1)),
            'inactive' => Tab::make('Productes desactivats')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('active', 0)),
        ];
    }
}
