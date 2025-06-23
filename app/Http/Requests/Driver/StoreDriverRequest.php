<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDriverRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('drivers', 'email')
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'language' => 'nullable|string|max:50',
            'dob' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'hire_date' => 'nullable|date',
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
}
