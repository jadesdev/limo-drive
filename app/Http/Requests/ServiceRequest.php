<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:services,name',
            'banner_image' => 'nullable|file|max:7048',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',

            // Problem Solved Section
            'problem_solved_image' => 'nullable|file|max:7048',
            'problem_solved_desc' => 'nullable|string',

            // Target Audience Section
            'target_audience_image' => 'nullable|file|max:7048',
            'target_audience_desc' => 'nullable|string',

            // Client Benefits Section
            'client_benefits_image' => 'nullable|file|max:7048',
            'client_benefits_desc' => 'nullable|string',

            'features' => 'required|array',
            'features.*' => 'required|string',
            'technologies' => 'required|array',
            'technologies.*' => 'required|string',
            'order' => 'sometimes|integer|min:0',
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
