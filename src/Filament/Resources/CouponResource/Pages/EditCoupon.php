<?php

namespace ApproTickets\Filament\Resources\CouponResource\Pages;

use ApproTickets\Filament\Resources\CouponResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
