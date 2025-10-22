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
            'child_name' => 'required|string|max:255|min:2',
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'birthdate' => 'required|date|before_or_equal:today|after:1900-01-01',
            'birth_height' => 'required|numeric|min:0|max:999.99',
            'birth_weight' => 'required|numeric|min:0|max:99.999',
            'birthplace' => 'nullable|string|max:255'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'child_name.required' => 'Child name is required.',
            'child_name.min' => 'Child name must be at least 2 characters.',
            'birthdate.required' => 'Birth date is required.',
            'birthdate.before_or_equal' => 'Birth date cannot be in the future.',
            'birthdate.after' => 'Please enter a valid birth date.',
            'gender.required' => 'Gender selection is required.',
            'birth_height.required' => 'Birth height is required.',
            'birth_height.numeric' => 'Birth height must be a valid number.',
            'birth_weight.required' => 'Birth weight is required.',
            'birth_weight.numeric' => 'Birth weight must be a valid number.',
        ];
    }
}
