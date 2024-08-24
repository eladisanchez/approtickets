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

        // Cleaning cart items
        Booking::where('order_id', NULL)
            ->where('created_at', '<', date('Y-m-d H:i:s', strtotime('-30 minutes')))
            ->delete();

        // Cleaning non paid orders
        $date = new \DateTime;
		$date->modify('-60 minutes');
		$formatted = $date->format('Y-m-d H:i:s');
		Order::where('paid', '!=', 1)
			->where('payment', 'card')
			->where('created_at', '<=', $formatted)
			->delete();

        $this->info("Done");

    }
}