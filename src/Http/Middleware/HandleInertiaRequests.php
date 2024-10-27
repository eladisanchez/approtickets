<?php

namespace ApproTickets\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Closure;
use ApproTickets\Models\Booking;
use ApproTickets\Http\Resources\CartItem;

class HandleInertiaRequests extends Middleware
{

    protected $rootView = 'app';

    protected function getCartData(): object
    {
        $cartItems = Booking::with('product')->where('order_id', NULL)
            ->where('session', session()->getId())
            ->whereNull('pack_booking_id')
            ->get();
        $cartTotal = $cartItems->sum(function ($item) {
            return $item->price * $item->tickets;
        });
        return (object) [
            'items' => $cartItems ?? [],
            'total' => $cartTotal,
        ];
    }

    public function handle(Request $request, Closure $next)
    {
        if (!config('approtickets.inertia')) {
            return $next($request);
        }

        $cartData = $this->getCartData();
        view()->share('cart', $cartData->items);
        view()->share('total', $cartData->total);

        return parent::handle($request, $next);
    }

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        
        $cartData = $this->getCartData();
        return array_merge(parent::share($request), [
            'csrf_token' => csrf_token(),
            'cart' => [
                'items' => CartItem::collection($cartData->items),
                'total' => $cartData->total 
            ]
        ]);
    }
}
