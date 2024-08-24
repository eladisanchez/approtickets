<?php

namespace ApproTickets\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use ApproTickets\Models\Booking;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $cartItems;
    protected $cartTotal;

    public function __construct()
    {
        $this->initializeCart();
    }

    protected function initializeCart()
    {
        $this->cartItems = Booking::where('order_id', NULL)
            ->where('session', session()->getId())
            ->get();

        $this->cartTotal = $this->cartItems->sum(function ($item) {
            return $item->price;
        });

        view()->share('cartItems', $this->cartItems);
        view()->share('cartTotal', $this->cartTotal);
    }
}