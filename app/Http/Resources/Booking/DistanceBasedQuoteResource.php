<?php

namespace App\Http\Resources\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistanceBasedQuoteResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'seats' => $this->seats,
            'bags' => $this->bags,
            'thumbnail_url' => $this->thumbnail_url,
            'image_urls' => $this->image_urls,
            'price' => $this->price,
            'estimated_duration' => $this->estimated_duration,
            'price_breakdown' => [
                'base_fare' => $this->price_breakdown['base_fare'],
                'surcharges' => $this->price_breakdown['surcharges'],
                'total' => $this->price_breakdown['total'],
            ],
            'distance' => [
                'miles' => $this->distance['miles'],
                'minutes' => $this->distance['minutes'],
                'description' => $this->distance['description'],
            ],
        ];
    }
}
