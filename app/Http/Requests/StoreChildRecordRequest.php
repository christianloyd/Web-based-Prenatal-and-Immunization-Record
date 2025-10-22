<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChildRecordRequest extends FormRequest
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
        $baseRules = [
            'first_name' => 'required|string|max:255|min:2',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255|min:2',
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'birthdate' => 'required|date|before_or_equal:today|after:1900-01-01',
            'birth_height' => 'required|numeric|min:0|max:999.99',
            'birth_weight' => 'required|numeric|min:0|max:99.999',
            'birthplace' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255|min:2',
            'phone_number' => ['required', 'string', 'max:13', 'regex:/^(\+63|0)[0-9]{10}$/'],
            'address' => 'nullable|string|max:1000',
            'mother_exists' => 'required|in:yes,no'
        ];

        // Add conditional validation based on mother_exists
        if ($this->input('mother_exists') === 'yes') {
            $baseRules['mother_id'] = 'required|exists:patients,id';
        } else {
            $baseRules['mother_name'] = 'required|string|max:255|min:2';
            $baseRules['mother_age'] = 'required|integer|min:15|max:50';
            $baseRules['mother_contact'] = ['required', 'string', 'max:13', 'regex:/^(\+63|0)[0-9]{10}$/'];
            $baseRules['mother_address'] = 'required|string|max:1000';
        }

        return $baseRules;
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.min' => 'First name must be at least 2 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'mother_name.required' => 'Mother\'s name is required when adding a new mother.',
            'mother_name.min' => 'Mother\'s name must be at least 2 characters.',
            'mother_age.required' => 'Mother\'s age is required when adding a new mother.',
            'mother_age.min' => 'Mother must be at least 15 years old.',
            'mother_age.max' => 'Mother cannot be older than 50 years.',
            'mother_contact.required' => 'Mother\'s contact number is required when adding a new mother.',
            'mother_contact.regex' => 'Please enter a valid Philippine mobile number (e.g., +639123456789 or 09123456789).',
            'mother_address.required' => 'Mother\'s address is required when adding a new mother.',
            'mother_id.required' => 'Please select a mother from the list.',
            'mother_id.exists' => 'Selected mother does not exist.',
            'phone_number.required' => 'Phone number is required.',
            'phone_number.regex' => 'Please enter a valid Philippine mobile number (e.g., +639123456789 or 09123456789).',
            'birthdate.required' => 'Birth date is required.',
            'birthdate.before_or_equal' => 'Birth date cannot be in the future.',
            'gender.required' => 'Gender selection is required.',
            'birth_height.required' => 'Birth height is required.',
            'birth_weight.required' => 'Birth weight is required.',
            'mother_exists.required' => 'Please specify if mother exists in the system.',
        ];
    }
}
