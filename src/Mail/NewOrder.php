<?php

namespace ApproTickets\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use ApproTickets\Models\Order;
use ApproTickets\Models\Option;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewOrder extends Mailable
{
    use Queueable, SerializesModels;

    private $text;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $text = Option::text('new-order-text');
        $this->text = strtr($text, [
            '[nom_client]' => $order->name,
            '[link_pdf]' => route('order.pdf', [$order->session, $order->id])
        ]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('approtickets::emails.template')->with('text', $this->text)
            ->subject('Les teves entrades');
    }
}
