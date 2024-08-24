<?php

namespace ApproTickets\Console\Commands;

use Illuminate\Console\Command;
use ApproTickets\Models\Booking;
use ApproTickets\Models\Order;

class CleanCartCommand extends Command
{
    // El nom i la signatura de la comanda Artisan.
    protected $signature = 'approtickets:clean-cart';

    // La descripció de la comanda Artisan.
    protected $description = 'Cleans abandoned cart items';

    // Execució de la comanda.
    public function handle()
    {

        $this->info("Cleaning old cart items");

        $ticketTimeout = config('approtickets.ticket_timeout');
        $paymentTimeout = config('approtickets.payment_timeout');

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
            foreach ($order->bookings as $booking) {
                $booking->delete();
            }
            $order->delete();
        }

        $this->info("Done");

    }
}