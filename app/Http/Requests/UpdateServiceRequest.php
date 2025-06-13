<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
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
        $serviceId = $this->route('service')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'name')->ignore($serviceId),
            ],
            // Images are optional on update - only validate if provided
            'banner_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:7048',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'attributes' => 'nullable|array',

            // Problem Solved Section - all optional on update
            'attributes.problem_solved' => 'sometimes|nullable|array',
            'attributes.problem_solved.image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:7048',
            'attributes.problem_solved.title' => 'sometimes|nullable|string|max:255',
            'attributes.problem_solved.description' => 'sometimes|nullable|string',

            // Target Audience Section - all optional on update
            'attributes.target_audience' => 'sometimes|nullable|array',
            'attributes.target_audience.image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:7048',
            'attributes.target_audience.title' => 'sometimes|nullable|string|max:255',
            'attributes.target_audience.description' => 'sometimes|nullable|string',

            // Client Benefits Section - all optional on update
            'attributes.client_benefits' => 'sometimes|nullable|array',
            'attributes.client_benefits.image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:7048',
            'attributes.client_benefits.title' => 'sometimes|nullable|string|max:255',
            'attributes.client_benefits.description' => 'sometimes|nullable|string',

            'features' => 'sometimes|array',
            'features.*' => 'sometimes|string',
            'technologies' => 'sometimes|array',
            'technologies.*' => 'sometimes|string',
            'order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The service name is required.',
            'name.unique' => 'A service with this name already exists.',
            'banner_image.image' => 'The banner must be a valid image file.',
            'banner_image.max' => 'The banner image must not exceed 7MB.',
            'description.required' => 'The service description is required.',
            'features.required' => 'At least one feature is required.',
            'features.*.required' => 'Each feature cannot be empty.',
            'technologies.required' => 'At least one technology is required.',
            'technologies.*.required' => 'Each technology cannot be empty.',
            'is_active.boolean' => 'The is_active field must be a boolean.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert comma-separated strings to arrays if needed
        if ($this->has('features') && is_string($this->features)) {
            $this->merge([
                'features' => array_filter(explode(',', $this->features)),
            ]);
        }

        if ($this->has('technologies') && is_string($this->technologies)) {
            $this->merge([
                'technologies' => array_filter(explode(',', $this->technologies)),
            ]);
        }

    }
}
