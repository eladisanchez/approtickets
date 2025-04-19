<?php

namespace ApproTickets\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandlePanelAccess
{
  public function handle(Request $request, Closure $next): Response
  {
    try {
      return $next($request);
    } catch (\Exception $e) {
      if (str_contains($e->getMessage(), 'You do not have permission to access this panel')) {
        return redirect()->route('home')->with('error', 'You do not have permission to access the admin panel.');
      }

      throw $e;
    }
  }
}