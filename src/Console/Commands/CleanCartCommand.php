<?php

namespace ApproTickets\Console\Commands;

use Illuminate\Console\Command;
use ApproTickets\Models\Booking;
use ApproTickets\Models\Order;

class CleanCartCommand extends Command
{

    protected $signature = 'approtickets:clean-cart';

    protected $description = 'Cleans abandoned cart items';

    public function handle()
    {

        $this->info("Cleaning old cart items");

        $ticketTimeout = config('approtickets.timeout.ticket');
        $paymentTimeout = config('approtickets.timeout.payment');

        // Cleaning cart items
        Booking::where('order_id', NULL)
            ->where('created_at', '<', date('Y-m-d H:i:s', strtotime("-{$ticketTimeout} minutes")))
            ->delete();

        // Cleaning non paid orders
        $date = new \DateTime;
        $date->modify("-{$paymentTimeout} minutes");
        $formatted = $date->format('Y-m-d H:i:s');
        $abandonedOrders = Order::where('paid', '!=', 1)
            ->where('payment', 'card')
            ->where('created_at', '<=', $formatted)
            ->get();
        foreach ($abandonedOrders as $order) {
            $order->delete();
        }

        $this->info("Done");

    }
}