<?php

namespace Approtickets\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Redsys\Tpv\Tpv;
use Mail;
use Log;
use Approtickets\Mail\NewOrder;
use Approtickets\Models\Order;
use Approtickets\Mail\NewOrderAlert;

class TPVController extends BaseController
{

    public function notification(): void
    {
        $TPV = new Tpv(config('redsys'));

        Log::info('Notificació del TPV', $_POST);

        try {
            $data = $TPV->checkTransaction($_POST);
            if (!$data['Ds_Order']) {
                return;
            }

            $orderId = substr($data['Ds_Order'], 0, -3);

            Log::info('Comanda', $data);
            $this->orderNotification($orderId, $data);

        } catch (\Exception $e) {
            $data = $TPV->getTransactionParameters($_POST);
            Log::error('Error en la resposta del TPV: ' . $e->getMessage(), $data);
        }

    }

    public function orderNotification($orderId, $data)
    {

        $order = Order::findOrFail($orderId);

        // Pagament correcte
        if ($data["Ds_Response"] <= 99):

            $order->update([
                'tpv_id' => $data["Ds_Order"],
                'paid' => 1
            ]);
            try {
                Mail::to($order->email)->send(new NewOrder($order));
                Mail::to(config('mail.from.address'))->send(new NewOrderAlert($order));
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }

            // Pagament fallit
        else:

            if ($order->paid != 1) {
                $order->paid = 2;
                $order->save();
            }

        endif;

    }

}