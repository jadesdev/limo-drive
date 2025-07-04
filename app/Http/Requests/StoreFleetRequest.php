<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFleetRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:fleets,slug',
            'description' => 'nullable|string',
            'thumbnail' => 'required|file|max:10048',
            'seats' => 'required|integer|min:1|max:20',
            'bags' => 'required|integer|min:0|max:50',
            'images' => 'nullable|array',
            'images.*' => 'file|max:10048',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'specifications' => 'nullable|array',
            'specifications.*.label' => 'required|string|max:255',
            'specifications.*.value' => 'required|string|max:255',
            'order' => 'nullable|integer|min:1',
            'base_fee' => 'nullable|numeric',
            'rate_per_mile' => 'nullable|numeric',
            'rate_per_hour' => 'nullable|numeric',
            'minimum_hours' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Fleet name is required',
            'seats.required' => 'Number of seats is required',
            'seats.min' => 'Fleet must have at least 1 seat',
            'bags.required' => 'Number of bags is required',
            'thumbnail.required' => 'Thumbnail Image is required',
            'thumbnail.image' => 'Thumbnail must be an image file',
            'thumbnail.max' => 'Thumbnail must not exceed 2MB',
            'images.*.image' => 'All uploaded files must be images',
            'images.*.max' => 'Each image must not exceed 2MB',
            'specifications.*.label.required' => 'Specification label is required',
            'specifications.*.value.required' => 'Specification value is required',
        ];
    }
}
