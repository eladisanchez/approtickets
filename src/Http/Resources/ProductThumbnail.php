<?php

namespace ApproTickets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductThumbnail extends JsonResource
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
            'name' => $this->name,
            'image' => $this->image,
            'is_pack' => $this->is_pack
        ];
    }
}
