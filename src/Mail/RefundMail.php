<?php

namespace ApproTickets\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use ApproTickets\Models\Refund;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;

class RefundMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        protected Refund $refund,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Devolució entrades {$this->refund->product->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.refund',
            with: [
                'refund' => $this->refund
            ],
        );
    }
}
