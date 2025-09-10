<?php

namespace ApproTickets\Console\Commands;

use Illuminate\Console\Command;
use ApproTickets\Models\Order;
use Mail;
use Log;
use ApproTickets\Mail\NewOrder;

class SendMailsCommand extends Command
{

    protected $signature = 'approtickets:send-mails';

    protected $description = 'Send mails for non sent orders';

    public function handle()
    {

        $this->info("Sending emails");

        $orders = Order::whereNull('email_sent_at')
            ->where('payment', 'card')
            ->where('paid', 1)
            ->where('created_at', '>', '2025-09-08 00:00:00')
            ->limit(10)
            ->get();

        foreach ($orders as $order) {
            $this->line("Sending {$order->id} - {$order->email}");
            try {
                Mail::to($order->email)->send(new NewOrder($order));
                $order->email_sent_at = now();
                $order->save();
                $this->line("Email sent to {$order->id} - {$order->email}");
            } catch (\Exception $e) {
                $this->line("Error sending to {$order->id} - {$order->email}");
                Log::error($e->getMessage());
            }
            sleep(1);

            // else {
            //     try {
            //         Mail::to($order->email)->send(new PaymentMail($order));
            //         $order->email_sent_at = '2025-09-08 00:00:00';
            //         $order->save();
            //         $this->line("Email sent to {$order->id} - {$order->email}");
            //     } catch (\Exception $e) {
            //         $this->line("Error sending to {$order->id} - {$order->email}");
            //         Log::error($e->getMessage());
            //     }
            // }
            
        }

        $this->info("Done");

    }
}