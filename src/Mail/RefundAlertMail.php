<?php

namespace ApproTickets\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use ApproTickets\Models\Refund;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;

class RefundAlertMail extends Mailable
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
            subject: 'Nova devolució',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'approtickets::emails.new-refund-alert',
            with: [
                'refund' => $this->refund
            ],
        );
    }
}
