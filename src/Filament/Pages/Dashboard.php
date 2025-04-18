<?php

namespace ApproTickets\Filament\Pages;

use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Forms\Form;

class Dashboard extends \Filament\Pages\Dashboard
{
  protected static ?string $title = 'Inici';

  use HasFiltersForm;

  public function filtersForm(Form $form): Form
  {
    return $form;
  }

  public static function shouldRegisterNavigation(): bool
  {
    return auth()->user()->hasRole('admin');
  }

}