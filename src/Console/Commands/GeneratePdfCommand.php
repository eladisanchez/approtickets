<?php

namespace ApproTickets\Console\Commands;

use Illuminate\Console\Command;
use ApproTickets\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use ApproTickets\Models\Option;

class GeneratePdfCommand extends Command
{

    protected $signature = 'approtickets:generate-pdf';

    protected $description = 'Generate PDFs';

    public function handle()
    {

        $this->info("Generating PDFs");

        $orders = Order::all();

        foreach ($orders as $order) {
            $this->line("Generating {$order->id} - {$order->email}");
            $pdfPath = storage_path("app/tickets/entrades-{$order->id}.pdf");
            if (!file_exists($pdfPath)) {
                $conditions = Option::where('key', 'condicions-venda')->pluck('value')->first();
                $pdf = Pdf::setOptions(['isRemoteEnabled' => true])->loadView(
                    'pdf.order',
                    [
                        'order' => $order,
                        'conditions' => $conditions
                    ]
                );
                $pdf->save($pdfPath);
                $this->line("Generated {$order->id} - {$order->email}");
            } else {
                $this->line("Skipping {$order->id} - {$order->email}");
            }
        }

        $this->info("Done");

    }
}