<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVaccineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['midwife', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:vaccines,name'],
            'category' => ['required', 'string', 'in:Routine Immunization,COVID-19,Seasonal,Travel'],
            'dosage' => ['required', 'string', 'max:255'],
            'dose_count' => ['required', 'integer', 'min:1', 'max:5'],
            'initial_stock' => ['nullable', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'expiry_date' => ['required', 'date', 'after:today'],
            'storage_temp' => ['required', 'string', 'in:2-8°C,15-25°C'],
            'notes' => ['nullable', 'string', 'max:1000']
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Vaccine name is required.',
            'name.unique' => 'This vaccine name already exists.',
            'category.required' => 'Category is required.',
            'category.in' => 'Please select a valid category.',
            'dosage.required' => 'Dosage is required.',
            'dose_count.required' => 'Number of doses is required.',
            'dose_count.min' => 'Number of doses must be at least 1.',
            'dose_count.max' => 'Number of doses cannot exceed 5.',
            'expiry_date.required' => 'Expiry date is required.',
            'expiry_date.after' => 'Expiry date must be in the future.',
            'storage_temp.required' => 'Storage temperature is required.',
            'storage_temp.in' => 'Please select a valid storage temperature.'
        ];
    }
}
