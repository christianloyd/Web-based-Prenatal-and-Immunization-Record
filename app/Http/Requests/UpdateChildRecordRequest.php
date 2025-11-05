<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChildRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['midwife', 'bhw']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s\.\-\']+$/'
            ],
            'middle_name' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\.\-\']+$/'
            ],
            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s\.\-\']+$/'
            ],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'birthdate' => 'required|date|before_or_equal:today|after:1900-01-01',
            'birth_height' => 'nullable|numeric|min:0|max:999.99',
            'birth_weight' => 'nullable|numeric|min:0|max:99.999',
            'birthplace' => 'nullable|string|max:255',
            'father_name' => [
                'nullable',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s\.\-\']+$/'
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.min' => 'First name must be at least 2 characters.',
            'first_name.regex' => 'First name should only contain letters, spaces, dots, hyphens, and apostrophes.',
            'middle_name.regex' => 'Middle name should only contain letters, spaces, dots, hyphens, and apostrophes.',
            'last_name.required' => 'Last name is required.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'last_name.regex' => 'Last name should only contain letters, spaces, dots, hyphens, and apostrophes.',
            'father_name.regex' => 'Father\'s name should only contain letters, spaces, dots, hyphens, and apostrophes.',
            'birthdate.required' => 'Birth date is required.',
            'birthdate.before_or_equal' => 'Birth date cannot be in the future.',
            'birthdate.after' => 'Please enter a valid birth date.',
            'gender.required' => 'Gender selection is required.',
            'birth_height.numeric' => 'Birth height must be a valid number.',
            'birth_weight.numeric' => 'Birth weight must be a valid number.',
        ];
    }
}
