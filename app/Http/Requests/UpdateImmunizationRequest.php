<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateImmunizationRequest extends FormRequest
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
            'schedule_date' => 'required|date',
            'schedule_time' => [
                'required',
                'date_format:H:i',
                'after_or_equal:05:00',
                'before:17:00',
            ],
            'status' => ['required', Rule::in(['Upcoming', 'Done', 'Missed'])],
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
            'schedule_time.required' => 'Schedule time is required.',
            'schedule_time.date_format' => 'Please enter a valid time format (HH:MM).',
            'schedule_time.after_or_equal' => 'Clinic hours start at 5:00 AM. Please choose a time after 5:00 AM.',
            'schedule_time.before' => 'Clinic hours end at 5:00 PM. Please choose a time before 5:00 PM.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
            'notes.required' => 'Notes are required.',
            'notes.max' => 'Notes cannot exceed 1000 characters.'
        ];
    }
}
