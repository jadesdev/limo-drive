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
        $rules = [
            'service_type' => ['required', 'string', Rule::in(['point_to_point', 'airport_pickup', 'airport_transfer', 'round_trip', 'wedding', 'event', 'other'])],
            'pickup_address' => ['required', 'string', 'max:255'],
            'passenger_count' => ['required', 'integer', 'min:1'],
            'bag_count' => ['required', 'integer', 'min:0'],
            /** required if service type is point_to_point, airport_pickup, airport_transfer, or round_trip */
            'dropoff_address' => ['required_if:service_type,point_to_point,airport_pickup,airport_transfer,round_trip', 'string', 'max:255'],
            /** required if service type is wedding, event, or other */
            'duration_hours' => ['required_if:service_type,wedding,event,other', 'integer', 'min:1'],
        ];
        return $rules;
    }
}
