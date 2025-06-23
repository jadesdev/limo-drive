<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetQuoteRequest extends FormRequest
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
        $serviceType = $this->input('service_type');

        $rules = [
            'service_type' => ['required', 'string', Rule::in(['point_to_point', 'airport_pickup', 'airport_transfer', 'round_trip', 'wedding', 'event', 'other'])],
            'pickup_address' => ['required', 'string', 'max:255'],
            'passenger_count' => ['required', 'integer', 'min:1'],
            'bag_count' => ['required', 'integer', 'min:0'],
            'dropoff_address' => ['nullable', 'string', 'max:255'],
        ];

        // Add rules specific to distance-based services
        if (in_array($serviceType, ['point_to_point', 'airport_pickup', 'airport_transfer', 'round_trip'])) {
            $rules['dropoff_address'] = ['required', 'string', 'max:255'];
        }

        // Add rules specific to hourly-based services
        if (in_array($serviceType, ['wedding', 'event', 'other'])) {
            $rules['duration_hours'] = ['required', 'integer', 'min:1'];
        }

        return $rules;
    }
}
