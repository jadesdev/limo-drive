<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Booking Choices
            'service_type' => ['sometimes', 'string', Rule::in(['point_to_point', 'airport_pickup', 'airport_transfer', 'round_trip', 'wedding', 'event', 'other'])],
            'fleet_id' => ['sometimes', 'uuid', 'exists:fleets,id'],
            // Customer Details
            'customer' => ['sometimes', 'array'],
            'customer.first_name' => ['sometimes', 'string', 'max:255'],
            'customer.last_name' => ['sometimes', 'string', 'max:255'],
            'customer.email' => ['required_with:customer.first_name,customer.last_name,customer.phone', 'email:rfc,dns', 'max:255'],
            'customer.phone' => ['sometimes', 'string', 'regex:/^\+?[0-9\s\-\(\)]{10,20}$/'],
            'customer.language' => ['sometimes', 'string', 'max:30'],

            // Trip Details
            'pickup' => ['sometimes', 'array'],
            'pickup.datetime' => ['sometimes', 'date', 'after_or_equal:now'],
            'pickup.address' => ['sometimes', 'string', 'max:255'],
            'pickup.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'pickup.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'dropoff' => ['sometimes', 'nullable', 'array'],
            'dropoff.address' => ['nullable', 'string', 'max:255'],
            'dropoff.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'dropoff.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'passengers' => ['sometimes', 'integer', 'min:1'],
            'bags' => ['sometimes', 'integer', 'min:0'],
            'accessible' => ['sometimes', 'boolean'],
            'return_service' => ['sometimes', 'boolean'],
            // pricing
            'payment' => ['sometimes', 'array'],
            'payment.method' => ['sometimes', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            // additional info
            'notes' => ['nullable', 'string', 'max:1000'],
            'duration_hours' => ['sometimes', 'integer', 'min:1', 'max:24'],
        ];
    }
}
