<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImmunizationRequest extends FormRequest
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
            'child_record_id' => 'required|exists:child_records,id',
            'vaccine_id' => 'required|exists:vaccines,id',
            'dose' => 'required|string|max:255',
            'schedule_date' => 'required|date|after_or_equal:today',
            'schedule_time' => 'required|date_format:H:i',
            'notes' => 'required|string|max:1000'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'child_record_id.required' => 'Please select a child.',
            'child_record_id.exists' => 'The selected child is invalid.',
            'vaccine_id.required' => 'Please select a vaccine.',
            'vaccine_id.exists' => 'The selected vaccine is invalid.',
            'dose.required' => 'Please select a dose.',
            'dose.max' => 'Dose description cannot exceed 255 characters.',
            'schedule_date.required' => 'Schedule date is required.',
            'schedule_date.date' => 'Please enter a valid date.',
            'schedule_date.after_or_equal' => 'Schedule date must be today or a future date.',
            'schedule_time.required' => 'Schedule time is required.',
            'schedule_time.date_format' => 'Please enter a valid time format (HH:MM).',
            'notes.required' => 'Notes are required.',
            'notes.max' => 'Notes cannot exceed 1000 characters.'
        ];
    }
}
