<?php

namespace ApproTickets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use ApproTickets\Enums\PaymentStatus;

class Order extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $url = $this->paid == PaymentStatus::PAID ?
            route('order.pdf', ['session' => $this->session, 'id' => $this->id]) :
            route('order.payment', ['id' => $this->id]);
        return [
            'id' => $this->id,
            'created_at' => $this->created_at->format('d/m/Y'),
            'tickets' => $this->totalTickets(),
            'total' => $this->total,
            'paid' => $this->paid,
            'url' => $url,
        ];
    }
}
