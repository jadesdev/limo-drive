<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            // Booking Choices
            'service_type' => ['required', 'string', Rule::in(['point_to_point', 'airport_pickup', 'airport_transfer', 'round_trip', 'wedding', 'event', 'other'])],
            'fleet_id' => ['required', 'uuid', 'exists:fleets,id'],
            // Customer Details
            'customer' => ['required', 'array'],
            'customer.first_name' => ['required', 'string', 'max:255'],
            'customer.last_name' => ['required', 'string', 'max:255'],
            'customer.email' => ['required', 'email:rfc,dns', 'max:255'],
            'customer.phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-\(\)]{10,20}$/'],

            // Trip Details
            'pickup' => ['required', 'array'],
            'pickup.datetime' => ['required', 'date', 'after_or_equal:now'],
            'pickup.address' => ['required', 'string', 'max:255'],
            'pickup.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'pickup.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'dropoff' => ['required_if:service_type,point_to_point,airport_transfer,round_trip', 'nullable', 'array'],
            'dropoff.address' => ['nullable', 'string', 'max:255'],
            'dropoff.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'dropoff.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'passengers' => ['required', 'integer', 'min:1'],
            'bags' => ['required', 'integer', 'min:0'],
            'accessible' => ['sometimes', 'boolean'],
            'return_service' => ['sometimes', 'boolean'],
            // pricing
            // additional info
            'notes' => ['nullable', 'string', 'max:1000'],
            'duration_hours' => ['required_if:service_type,wedding,event,other', 'nullable', 'integer', 'min:1', 'max:24'],
        ];
    }

    public function messages(): array
    {
        return [
            // Service Type
            'service_type.required' => 'Please select a service type',
            'service_type.in' => 'The selected service type is invalid',

            // Fleet
            'fleet_id.required' => 'Please select a vehicle',
            'fleet_id.uuid' => 'Invalid vehicle selection',
            'fleet_id.exists' => 'The selected vehicle is not available',

            // Customer
            'customer.required' => 'Customer information is required',
            'customer.first_name.required' => 'First name is required',
            'customer.first_name.max' => 'First name cannot exceed 100 characters',
            'customer.last_name.required' => 'Last name is required',
            'customer.last_name.max' => 'Last name cannot exceed 100 characters',
            'customer.email.required' => 'Email is required',
            'customer.email.email' => 'Please enter a valid email address',
            'customer.phone.required' => 'Phone number is required',
            'customer.phone.regex' => 'Please enter a valid phone number',

            // Pickup
            'pickup.datetime.required' => 'Pickup date and time are required',
            'pickup.datetime.after_or_equal' => 'Pickup time must be in the future',
            'pickup.address.required' => 'Pickup address is required',
            'pickup.longitude.between' => 'Invalid longitude value',
            'pickup.latitude.between' => 'Invalid latitude value',

            // Dropoff
            'dropoff.required_if' => 'Dropoff information is required for this service type',
            'dropoff.address.required_with' => 'Dropoff address is required',

            // Capacity
            'passengers.required' => 'Please specify number of passengers',
            'passengers.min' => 'At least 1 passenger is required',
            'passengers.max' => 'Maximum 20 passengers allowed',
            'bags.required' => 'Please specify number of bags',
            'bags.min' => 'Bag count cannot be negative',
            'bags.max' => 'Maximum 10 bags allowed',

            // Payment
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method',
            'payment.intent_id.required_if' => 'Payment intent is required for card payments',
            'payment.intent_id.starts_with' => 'Invalid payment intent',
            'price.required' => 'Price is required',
            'price.min' => 'Minimum price is $10',
            'price.max' => 'Maximum price exceeded',

            // Duration
            'duration_hours.required_if' => 'Duration is required for this service type',
            'duration_hours.min' => 'Minimum booking duration is 1 hour',
            'duration_hours.max' => 'Maximum booking duration is 24 hours',
        ];
    }
}
