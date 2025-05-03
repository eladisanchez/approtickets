<?php

namespace ApproTickets\Console\Commands;

use Illuminate\Console\Command;
use ApproTickets\Models\Order;
use Mail;
use Log;
use ApproTickets\Mail\RememberMail;
use ApproTickets\Enums\PaymentStatus;

class SendMailsCommand extends Command
{

    protected $signature = 'approtickets:send-mails';

    protected $description = 'Cleans abandoned cart items';

    public function handle()
    {

        $this->info("Sending emails");

        $orders = Order::where('payment', 'card')
            ->where('created_at', '>', '2025-02-06 00:00:00')
            ->get();

        foreach ($orders as $order) {
            $this->line("Sending {$order->id} - {$order->email}");
            if ($order->paid == PaymentStatus::PAID) {
                try {

                    $failedOrders = [1742, 1744, 1748, 1750, 1753, 1758, 1761, 1770, 1771, 1775, 1777, 1779];
                    $numTickets = 0;
                    $failed = in_array($order->id, $failedOrders);
                    if ($failed) {
                        $booking = $order->bookings->first();
                        $numTickets = $booking->tickets;
                        $booking->update([
                            'tickets' => 1
                        ]);
                        $this->line("Tickets fixed for {$order->id} - {$order->email}");
                    }
                    Mail::to($order->email)->send(new RememberMail($order, $failed, $numTickets));
                    $order->email_sent_at = now();
                    $order->save();
                    $this->line("Email sent to {$order->id} - {$order->email}");
                } catch (\Exception $e) {
                    $this->line("Error sending to {$order->id} - {$order->email}");
                    Log::error($e->getMessage());
                }
            }
            //sleep(1);
        }

        $this->info("Done");

    }
}