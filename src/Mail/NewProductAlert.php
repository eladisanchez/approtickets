<?php

namespace ApproTickets\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use ApproTickets\Models\Product;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;

class NewProductAlert extends Mailable
{
    use Queueable, SerializesModels;

    private $text;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        protected Product $product,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nova sol·licitud: ' . $this->product->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-product-alert',
            with: [
                'product' => $this->product
            ],
        );
    }
}
