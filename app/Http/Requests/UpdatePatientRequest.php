<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['bhw', 'midwife']);
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
                'max:50',
                'regex:/^[a-zA-Z\s\.\-\']+$/'
            ],
            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-zA-Z\s\.\-\']+$/'
            ],
            'age' => [
                'required',
                'integer',
                'min:15',
                'max:50'
            ],
            'occupation' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s\.\-\/]+$/'
            ],
            'contact' => [
                'required',
                'string',
                'max:13',
                'regex:/^(\+63|0)[0-9]{10}$/'
            ],
            'emergency_contact' => [
                'required',
                'string',
                'max:13',
                'regex:/^(\+63|0)[0-9]{10}$/'
            ],
            'address' => [
                'required',
                'string',
                'max:255'
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
            'first_name.max' => 'First name cannot exceed 50 characters.',
            'first_name.regex' => 'First name should only contain letters, spaces, dots, hyphens, and apostrophes.',

            'last_name.required' => 'Last name is required.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'last_name.max' => 'Last name cannot exceed 50 characters.',
            'last_name.regex' => 'Last name should only contain letters, spaces, dots, hyphens, and apostrophes.',

            'age.required' => 'Age is required.',
            'age.integer' => 'Age must be a valid number.',
            'age.min' => 'Age must be at least 15 years.',
            'age.max' => 'Age cannot exceed 50 years.',

            'occupation.required' => 'Occupation is required.',
            'occupation.max' => 'Occupation cannot exceed 50 characters.',
            'occupation.regex' => 'Occupation should only contain letters, spaces, dots, hyphens, and forward slashes.',

            'contact.required' => 'Primary contact is required.',
            'contact.max' => 'Contact number is too long.',
            'contact.regex' => 'Please enter a valid Philippine phone number (e.g., +639123456789 or 09123456789).',

            'emergency_contact.required' => 'Emergency contact is required.',
            'emergency_contact.max' => 'Emergency contact number is too long.',
            'emergency_contact.regex' => 'Please enter a valid Philippine phone number for emergency contact (e.g., +639123456789 or 09123456789).',

            'address.required' => 'Address is required.',
            'address.max' => 'Address cannot exceed 255 characters.'
        ];
    }
}
