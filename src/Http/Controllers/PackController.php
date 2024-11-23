<?php

namespace ApproTickets\Http\Controllers;
use ApproTickets\Models\Product;
use Illuminate\Routing\Controller as BaseController;

class PackController extends BaseController
{

    public function start($packId, $rateId)
    {
        $pack = Product::find($packId);
        $firstPackProduct = $pack->packProducts->first();
        session()->put("pack.{$packId}", $rateId);
        return redirect()->route('product', ['name' => $firstPackProduct->name]);
    }

    public function cancel($packId)
    {
        session()->forget("pack.{$packId}");
        return redirect()->back();
    }

}