<?php

namespace ApproTickets\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum PaymentStatus: int implements HasLabel, HasIcon, HasColor
{
  case UNPAID = 0;
  case PAID = 1;
  case FAILED = 2;
  case CART = 3;
  case REFUND = 4;

  public function getLabel(): ?string
  {
    return match ($this) {
      self::UNPAID => 'Pendent',
      self::PAID => 'Pagat',
      self::FAILED => 'Cancel·lat / error',
      self::CART => 'Cistell',
      self::REFUND => 'Devolució'
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::UNPAID => 'heroicon-o-clock',
      self::PAID => 'heroicon-o-check',
      self::FAILED => 'heroicon-o-x-mark',
      self::CART => 'heroicon-0-x-shopping-cart',
      self::REFUND => 'heroicon-o-backward'
    };
  }

  public function getColor(): ?string
  {
    return match ($this) {
      self::UNPAID => 'warning',
      self::PAID => 'success',
      self::FAILED => 'danger',
      self::CART => 'info',
      self::REFUND => 'info'
    };
  }
}