<?php

namespace Approtickets\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Approtickets\Models\Order;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;

class NewOrderAlert extends Mailable
{
    use Queueable, SerializesModels;

    private $text;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        protected Order $order,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nova comanda: '.$this->order->number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-order-alert',
            with: [
                'order' => $this->order
            ],
        );
    }
}
