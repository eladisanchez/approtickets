<?php

namespace ApproTickets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Banner extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->getTranslation('title', app()->getLocale()),
            'product' => $this->product->getTranslation('title', app()->getLocale()),
            'url' => $this->product->name,
            'image' => $this->product->image,
        ];
    }
}
