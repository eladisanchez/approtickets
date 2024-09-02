<?php

namespace ApproTickets\Console\Commands;

use Illuminate\Console\Command;
use ApproTickets\Models\Order;
use Mail;
use Log;
use ApproTickets\Mail\NewOrder;
use ApproTickets\Mail\PaymentMail;

class SendMailsCommand extends Command
{

    protected $signature = 'approtickets:send-mails';

    protected $description = 'Cleans abandoned cart items';

    public function handle()
    {

        $this->info("Sending emails");

        $orders = Order::where('email_sent', '!=', 1)
            ->where('payment', 'card')
            ->limit(10)
            ->get();

        foreach ($orders as $order) {
            $this->line("Sending {$order->id} - {$order->email}");
            if ($order->paid == 1) {
                try {
                    Mail::to($order->email)->send(new NewOrder($order));
                    $order->email_sent = 1;
                    $order->save();
                    $this->line("Email sent to {$order->id} - {$order->email}");
                } catch (\Exception $e) {
                    $this->error("Error sending to {$order->id} - {$order->email}");
                    Log::error($e->getMessage());
                }
            } else {
                if ($order->email_sent != 2) {
                    try {
                        Mail::to($order->email)->send(new PaymentMail($order));
                        $order->email_sent = 2;
                        $order->save();
                        $this->line("Email sent to {$order->id} - {$order->email}");
                    } catch (\Exception $e) {
                        $this->error("Error sending to {$order->id} - {$order->email}");
                        Log::error($e->getMessage());
                    }
                }
            }
            sleep(1);
        }

        $this->info("Done");

    }
}