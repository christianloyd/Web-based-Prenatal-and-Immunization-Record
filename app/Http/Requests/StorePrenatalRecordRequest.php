<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrenatalRecordRequest extends FormRequest
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
            'patient_id' => 'required|exists:patients,id',
            'last_menstrual_period' => 'required|date|before_or_equal:today',
            'expected_due_date' => 'nullable|date|after:last_menstrual_period',
            'gravida' => 'nullable|integer|min:1|max:10',
            'para' => 'nullable|integer|min:0|max:10',
            'medical_history' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:500',
            'blood_pressure' => 'required|string|max:20|regex:/^\d{2,3}\/\d{2,3}$/',
            'weight' => 'required|numeric|min:30|max:200',
            'height' => 'required|numeric|min:120|max:200',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'patient_id.required' => 'Please select a patient.',
            'patient_id.exists' => 'The selected patient does not exist.',
            'last_menstrual_period.required' => 'Last menstrual period is required.',
            'last_menstrual_period.date' => 'Please enter a valid date for last menstrual period.',
            'last_menstrual_period.before_or_equal' => 'Last menstrual period cannot be in the future.',
            'expected_due_date.date' => 'Please enter a valid expected due date.',
            'expected_due_date.after' => 'Expected due date must be after last menstrual period.',
            'gravida.integer' => 'Gravida must be a number.',
            'gravida.min' => 'Gravida must be at least 1.',
            'gravida.max' => 'Gravida cannot exceed 10.',
            'para.integer' => 'Para must be a number.',
            'para.min' => 'Para must be at least 0.',
            'para.max' => 'Para cannot exceed 10.',
            'medical_history.required' => 'Medical history is required.',
            'medical_history.max' => 'Medical history cannot exceed 1000 characters.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
            'blood_pressure.required' => 'Blood pressure is required.',
            'blood_pressure.max' => 'Blood pressure cannot exceed 20 characters.',
            'blood_pressure.regex' => 'Blood pressure must be in format: XXX/XXX (e.g., 120/80).',
            'weight.required' => 'Weight is required.',
            'weight.numeric' => 'Weight must be a number.',
            'weight.min' => 'Weight must be at least 30 kg.',
            'weight.max' => 'Weight cannot exceed 200 kg.',
            'height.required' => 'Height is required.',
            'height.numeric' => 'Height must be a number.',
            'height.min' => 'Height must be at least 120 cm.',
            'height.max' => 'Height cannot exceed 200 cm.',
        ];
    }
}
