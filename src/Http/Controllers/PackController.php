<?php

namespace ApproTickets\Http\Controllers;
use ApproTickets\Models\Product;
use ApproTickets\Models\Booking;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class PackController extends BaseController
{

    public static function redirectToNextProduct($pack)
    {
        $packProductIds = $pack->packProducts->modelKeys();
        $packRates = $pack->rates->modelKeys();
        foreach ($packProductIds as $packProductId) {
            $checkProductInCart = Booking::with('product')->where('order_id', NULL)
                ->where('session', session()->getId())
                ->whereNull('pack_booking_id')
                ->where('product_id', $packProductId)
                ->whereIn('rate_id', $packRates)->first();
            if (!$checkProductInCart) {
                $product = Product::find($packProductId);
                return $product->name;
            }
        }
        session()->forget("pack");
        return false;
    }

    public function start($packId, Request $request)
    {
        $pack = Product::find($packId);
        if (!session()->has("pack")) {
            $firstPackProduct = $pack->packProducts->first();
            session()->put('pack', [
                'id' => $packId,
                'title' => $pack->title,
                'rates' => $request->input('rates'),
                'qty' => $request->input('qty')
            ]);
            return redirect()->route('product', ['name' => $firstPackProduct->name]);
        }
        $nextProduct = $this->redirectToNextProduct($pack);
        if ($nextProduct) {
            return redirect()->route('product', ['name' => $nextProduct]);
        }
        return redirect()->route('checkout');
    }

    public function cancel()
    {
        session()->forget("pack");
        return redirect()->back();
    }

}