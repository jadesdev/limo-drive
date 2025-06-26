<?php

namespace App\Http\Resources\Booking;

use App\Http\Resources\DriverResource;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'service_type' => $this->service_type,
            // Pricing
            'price' => $this->price,
            // Customer details
            'customer' => [
                'first_name' => $this->customer_first_name,
                'last_name' => $this->customer_last_name,
                'full_name' => $this->name,
                'email' => $this->customer_email,
                'phone' => $this->customer_phone,
            ],

            // Trip details
            'pickup' => [
                'datetime' => $this->pickup_datetime?->toISOString(),
                'date' => $this->pickup_datetime?->format('Y-m-d'),
                'time' => $this->pickup_datetime?->format('H:i'),
                'formatted_datetime' => $this->pickup_datetime?->format('M j, Y \a\t g:i A'),
                'address' => $this->pickup_address,
                'coordinates' => [
                    'latitude' => $this->pickup_latitude,
                    'longitude' => $this->pickup_longitude,
                ],
            ],

            'dropoff' => $this->when($this->dropoff_address, [
                'address' => $this->dropoff_address,
                'coordinates' => [
                    'latitude' => $this->dropoff_latitude,
                    'longitude' => $this->dropoff_longitude,
                ],
            ]),

            // Booking details
            'passengers' => $this->passenger_count,
            'bags' => $this->bag_count,
            'duration_hours' => $this->duration_hours,
            'is_accessible' => $this->is_accessible,
            'is_return_service' => $this->is_return_service,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,

            'fleet' => new FleetResource($this->whenLoaded('fleet')),

            // Driver details
            'driver' => new DriverResource($this->whenLoaded('driver')),

            // Payment details
            'latest_payment' => new PaymentResource($this->whenLoaded('latestPayment')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
