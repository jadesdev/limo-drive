<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
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
            // Customer Details
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],

            // Trip Details (mirrors the quote request)
            'pickup_datetime' => ['required', 'date', 'after:now'],
            'pickup_address' => ['required', 'string', 'max:255'],
            'dropoff_address' => ['nullable', 'string', 'max:255'],
            'passenger_count' => ['required', 'integer', 'min:1'],
            'bag_count' => ['required', 'integer', 'min:0'],
            'notes_for_driver' => ['nullable', 'string', 'max:1000'],
            'duration_hours' => ['nullable', 'integer', 'min:1'], // For hourly services

            // Booking Choices
            'service_id' => ['required', 'uuid', 'exists:services,id'],
            'fleet_id' => ['required', 'uuid', 'exists:fleets,id'],
        ];
    }
}
