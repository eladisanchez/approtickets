<?php

namespace ApproTickets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItem extends JsonResource
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
            'qty' => $this->tickets,
            'seat' => $this->when($this->seat, function () {
                return [
                    's' => $this->seat,
                    'f' => $this->row
                ];
            }),
            'rate' => $this->rate->getTranslation('title', app()->getLocale()),
            'title' => $this->product->getTranslation('title', app()->getLocale()),
            'day' => $this->day,
            'hour' => $this->hour,
            'name' => $this->product->name,
            'image' => $this->product->image,
            'price' => $this->price,
            'is_pack' => $this->is_pack,
            'subtotal' => $this->price * $this->tickets,
            'packBookings' => $this->packBookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'title' => $booking->product->getTranslation('title', app()->getLocale()),
                    'day' => $booking->day,
                    'hour' => $booking->hour
                ];
            })
        ];
    }
}
