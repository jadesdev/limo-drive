<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'code' => $this->code,
            'booking_code' => $this->booking->code ?? 'N/A',
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'gateway' => $this->gateway_name,
            'gateway_ref' => $this->gateway_ref,
            'created_at' => $this->created_at->toIso8601String(),
        ];
        // return parent::toArray($request);
    }
}
