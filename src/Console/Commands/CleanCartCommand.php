<?php

namespace ApproTickets\Console\Commands;

use Illuminate\Console\Command;
use ApproTickets\Models\Booking;

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

        $cartItems = Booking::where('order_id', NULL)
            ->where('created_at', '<', date('Y-m-d H:i:s', strtotime('-30 minutes')))
            ->get();
        $cartItems->delete();

        $this->info("Done");

    }
}