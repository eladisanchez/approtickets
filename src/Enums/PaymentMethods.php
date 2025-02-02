<?php

namespace ApproTickets\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum PaymentMethods: string implements HasLabel, HasIcon
{
    case Card = 'card';
    case Credit = 'credit';
    case Cash = 'cash';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Card => __('approtickets::payment_methods.card'),
            self::Credit => __('approtickets::payment_methods.credit'),
            self::Cash => __('approtickets::payment_methods.cash')
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Card => 'heroicon-o-credit-card',
            self::Credit => 'heroicon-o-briefcase',
            self::Cash => 'heroicon-o-banknotes'
        };
    }

}
