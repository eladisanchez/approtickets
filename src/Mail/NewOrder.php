<?php

namespace Approtickets\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Approtickets\Models\Order;
use Approtickets\Helpers\Common;

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
        $text = Common::option('email_comanda');
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
        return $this->view('emails.order')->with('text', $this->text)
            ->subject('Les teves entrades');
    }
}
