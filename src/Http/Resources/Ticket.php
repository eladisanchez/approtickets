<?php

namespace ApproTickets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Ticket extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tickets' => $this->tickets,
            'day' => $this->day->format('Y-m-d'),
            'hour' => $this->hour->format('H:i'),
            'seats' => $this->whenHas('seats'),
            'bookings' => $this->bookingsTotal,
            'bookedSeats' => $this->bookedSeats,
            'cartSeats' => $this->cartSeats,
            
        ];
    }
}
