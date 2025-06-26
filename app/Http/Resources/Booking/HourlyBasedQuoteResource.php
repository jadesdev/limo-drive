<?php

namespace App\Http\Resources\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HourlyBasedQuoteResource extends JsonResource
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
            'booking_duration' => $this->booking_duration,
            'price_breakdown' => [
                'base_fare' => $this->price_breakdown['base_fare'],
                'surcharges' => $this->price_breakdown['surcharges'],
                /**
                 * Hourly rate per hour
                 * @var float
                 */
                'hourly_rate' => $this->price_breakdown['hourly_rate'],
                /**
                 * Total hours booked (may be higher than requested due to minimum hours)
                 * @var int
                 */
                'total_hours' => $this->price_breakdown['total_hours'],
                'total' => $this->price_breakdown['total'],
            ],
        ];
    }
}
