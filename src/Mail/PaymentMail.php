<?php

namespace ApproTickets\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use ApproTickets\Models\Order;
use ApproTickets\Models\Option;

class PaymentMail extends Mailable
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
        $paymentLink = route('order.payment', $order->id).'?'.$order->session;
        $text = "<p>Hola {$order->name},</p>
        <p>La vostra comanda no s'ha pogut completar durant el dia d'avui degut a problemes tècnics. Les vostres localitats escollides, però, han quedat reservades:</p><ul>";
        foreach ($order->bookings as $booking) {
            $productUrl = route('product', [
                'name'=> $booking->product->name,
                'day' => $booking->day->format('Y-m-d'),
                'hour' => $booking->hour
            ]);
            $text .= "<li><a href='{$productUrl}'>{$booking->product->title}</a> - {$booking->day->format('d/m/Y')} - Fila {$booking->row} seient {$booking->seat}</li>";
        }
        $text .= "</ul><p>Per completar la comanda tan sols heu d'utilitzar el següent enllaç i fer el pagament:</p>
        <p><a href='{$paymentLink}'>{$paymentLink}</a></p>
        <p>Teniu temps fins <strong>dimarts 3 de setembre a les 20:00</strong>. A partir d'aquesta hora les entrades s'alliberaran i algú altre les podrà adquirir.</p>
        <p>Per a qualsevol consulta us podeu adreçar a l'Oficina d'Atenció de la Festa Major (Centre Cardona Medieval - voltes de la plaça de la Fira)</p>";
        $this->text = $text;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.template')->with('text', $this->text)
            ->subject('Les teves entrades (pagament pendent)');
    }
}
