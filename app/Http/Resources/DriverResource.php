<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'language' => $this->language,
            'dob' => $this->dob ? $this->dob->format('Y-m-d') : null,
            'orders' => $this->orders,
            'gender' => $this->gender,
            'status' => $this->status,
            'hire_date' => $this->hire_date ? $this->hire_date->format('Y-m-d') : null,
            'termination_date' => $this->termination_date ? $this->termination_date->format('Y-m-d') : null,
            'notes' => $this->notes,
            'is_available' => (bool) $this->is_available,
            'last_online_at' => $this->last_online_at ? $this->last_online_at->toDateTimeString() : null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
