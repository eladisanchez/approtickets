<?php

namespace ApproTickets\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Http\Request;
use Closure;

class RedirectIfNotAuthorized
{
  public function handle(Request $request, Closure $next)
  {

    $canAccessPanel = auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasRole('organizer'));

    if ($canAccessPanel) {
      return $next($request);
    }

    return redirect()->route('checkout');
  }
}