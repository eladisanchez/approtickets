<?php

namespace ApproTickets\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use ApproTickets\Models\Order;
use ApproTickets\Models\Option;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;

class RememberMail extends Mailable
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
        protected $failed,
        protected $numTickets
    ) {
    }

    public function content(): Content
    {
        $text = Option::text('remember');

        $text_failed = "<p><strong>-- -- --</strong></p><p><strong>ATENCIÓ: Aquesta comanda era originàriament de {$this->numTickets} entrades, però per un error d'integració amb el TPV només se'n va cobrar una. Hem rectificat el PDF deixant una sola entrada, i us demanem que feu una comanda nova amb les entrades que us faltin. Disculpeu les molèsties.</strong></p><p><strong>-- -- --</strong></p>";
        $this->text = strtr($text, [
            '[nom_client]' => $this->order->name,
            '[link_pdf]' => route('order.pdf', [$this->order->session, $this->order->id]),
            '[failed]' => $this->failed ? $text_failed : ''
        ]);
        return new Content(
            view: 'emails.template',
            with: [
                'text' => $this->text
            ],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reenviament Entrades Carnaval (comanda {$this->order->id})",
        );
    }
}
