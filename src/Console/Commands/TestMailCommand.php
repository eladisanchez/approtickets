<?php

namespace ApproTickets\Console\Commands;

use Illuminate\Console\Command;
use ApproTickets\Models\Booking;
use ApproTickets\Models\Order;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{

    protected $signature = 'approtickets:test-mail';

    protected $description = 'Sends a test email';

    public function handle()
    {

        $this->info("Sending test email");

        Mail::raw('Funciona.', function ($message) {
            $message->to('eladisanchez@gmail.com')
                ->subject('El correu funciona');
        });

        $this->info("Done");

    }
}