<?php

namespace ApproTickets\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Redsys\Tpv\Tpv;
use Mail;
use Log;
use ApproTickets\Mail\NewOrder;
use ApproTickets\Models\Order;
use ApproTickets\Models\Payment;
use ApproTickets\Mail\NewOrderAlert;

class TPVController extends BaseController
{

    public function notification(): void
    {

        Log::info('Notificació rebuda');

        $TPV = new Tpv(config('redsys'));

        try {
            $data = $TPV->checkTransaction($_POST);
            Log::debug($data);
            if (!$data['Ds_Order']) {
                return;
            }

            $orderId = substr($data['Ds_Order'], 0, -3);
            $type = substr($data['Ds_Order'], -1);

            if ($type === '2'):
                $this->paymentNotification($orderId, $data);
            else:
                $this->orderNotification($orderId, $data);
            endif;

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
                $order->email_sent = 1;
                $order->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }

            // Pagament fallit
        else:

            Log::info('Error pagament');
            if ($order->paid != 1) {
                $order->paid = 2;
                $order->save();
            }

        endif;

    }

    public function paymentNotification($id, $data)
    {
        $payment = Payment::findOrFail($id);
        if ($data["Ds_Response"] <= 99):
            $payment->update([
                'tpv_id' => $data["Ds_Order"],
                'paid_at' => now()
            ]);
        endif;
    }

}