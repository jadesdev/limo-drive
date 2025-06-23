<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $driverId = $this->route('driver');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('drivers', 'email')->ignore($driverId),
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'language' => 'nullable|string|max:50',
            'dob' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'status' => 'sometimes|in:active,inactive,on_leave,suspended',
            'hire_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_available' => 'sometimes|boolean',
            'last_online_at' => 'sometimes|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.unique' => 'A driver with this email already exists.',
            'dob.before' => 'The date of birth must be a date before today.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->has('is_available')) {
            $this->merge([
                'is_available' => filter_var($this->is_available, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
