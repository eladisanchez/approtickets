<?php

namespace ApproTickets\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Redsys\Tpv\Tpv;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\View\View;
use ApproTickets\Models\Payment;

class PaymentController extends BaseController
{

    public function show(string $hash): View|InertiaResponse
    {
        $payment = Payment::where('hash', $hash)
            ->whereNotNull('paid_at')
            ->with('order')
            ->firstOrFail();
        if (config('approtickets.inertia')) {
            $content = view('partials.payment', [
                'payment' => $payment,
            ])->render();
            return Inertia::render('Basic', [
                'title' => __('Pagament'),
                'content' => $content
            ]);
        }
        return view('checkout.payment', [
            'payment' => $payment,
        ]);
    }

    public function pay($id)
    {
        $payment = Payment::with('order')->findOrFail($id);
        if ($payment->paid_at) {
            return view('payment', [
                'payment' => $payment
            ]);
        }

        $uniqid = str_pad(mt_rand(0, 99), 2, '0', STR_PAD_LEFT);

        $TPV = new Tpv(config('redsys'));
        $appName = config('app.name');
        $TPV->setFormHiddens(
            [
                'TransactionType' => '0',
                'MerchantData' => "{$appName} {$payment->description}",
                'MerchantURL' => route('tpv-notification'),
                'Order' => "{$payment->id}{$uniqid}2",
                'Amount' => $payment->total,
                'UrlOK' => route('payment', ['hash' => $payment->hash]),
                'UrlKO' => route('payment', ['hash' => $payment->hash]),
            ]
        );

        return view('payment', [
            'payment' => $payment,
            'TPV' => $TPV
        ]);
    }

}
