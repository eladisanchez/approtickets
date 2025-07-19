<?php

namespace ApproTickets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Product extends JsonResource
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
            'title' => $this->getTranslation('title', app()->getLocale()),
            'summary' => $this->getTranslation('summary', app()->getLocale()),
            'description' => $this->getTranslation('description', app()->getLocale()),
            'schedule' => $this->getTranslation('schedule', app()->getLocale()),
            'place' => $this->place,
            'url' => route('product', $this->name, false),
            'image' => $this->image,
            'image_header' => $this->image_header,
            'target' => $this->category->target,
            'organizer' => $this->when($this->organizer, function () {
                return [
                    'name' => $this->organizer?->name,
                ];
            }),
            'category' => [
                'title' => $this->category->getTranslation('title', app()->getLocale())
            ],
            'min_tickets' => $this->min_tickets,
            'max_tickets' => $this->max_tickets,
            'venue_id' => $this->venue_id,
            'packs' => $this->packs->modelKeys(),
            'hour_limit' => $this->hour_limit
        ];
    }
}
