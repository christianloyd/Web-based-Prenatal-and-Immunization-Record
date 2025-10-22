<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidBloodPressure;

class StorePrenatalCheckupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only midwife and bhw can create prenatal checkups
        return in_array(auth()->user()->role, ['midwife', 'bhw']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'checkup_date' => 'required|date',
            'checkup_time' => 'required|date_format:H:i',
            'gestational_age_weeks' => 'nullable|integer|min:1|max:45',
            'weight_kg' => 'required|numeric|min:30|max:200',
            'blood_pressure_systolic' => [
                'required',
                'integer',
                new ValidBloodPressure('systolic', $this->blood_pressure_diastolic)
            ],
            'blood_pressure_diastolic' => [
                'required',
                'integer',
                new ValidBloodPressure('diastolic')
            ],
            'fetal_heart_rate' => 'nullable|integer|min:100|max:180',
            'fundal_height_cm' => 'nullable|numeric|min:10|max:50',
            'presentation' => 'nullable|string|max:50',
            'baby_movement' => 'nullable|in:active,normal,less,none',
            'symptoms' => 'required|string|max:500',
            'notes' => 'required|string|max:1000',
            'next_visit_date' => 'nullable|date|after:checkup_date',
            'next_visit_time' => 'nullable|date_format:H:i',
            'next_visit_notes' => 'nullable|string|max:500',
            'conducted_by' => 'nullable|exists:users,id',
            'schedule_next' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'patient_id.required' => 'Please select a patient.',
            'patient_id.exists' => 'The selected patient does not exist.',
            'checkup_date.required' => 'Checkup date is required.',
            'checkup_time.required' => 'Checkup time is required.',
            'weight_kg.required' => 'Weight is required.',
            'weight_kg.min' => 'Weight must be at least 30 kg.',
            'weight_kg.max' => 'Weight cannot exceed 200 kg.',
            'blood_pressure_systolic.required' => 'Systolic blood pressure is required.',
            'blood_pressure_diastolic.required' => 'Diastolic blood pressure is required.',
            'symptoms.required' => 'Symptoms observation is required.',
            'notes.required' => 'Checkup notes are required.',
            'fetal_heart_rate.min' => 'Fetal heart rate must be at least 100 bpm.',
            'fetal_heart_rate.max' => 'Fetal heart rate cannot exceed 180 bpm.',
            'next_visit_date.after' => 'Next visit date must be after the checkup date.',
        ];
    }

    /**
     * Get custom attribute names for error messages
     */
    public function attributes(): array
    {
        return [
            'patient_id' => 'patient',
            'checkup_date' => 'checkup date',
            'checkup_time' => 'checkup time',
            'gestational_age_weeks' => 'gestational age',
            'weight_kg' => 'weight',
            'blood_pressure_systolic' => 'systolic blood pressure',
            'blood_pressure_diastolic' => 'diastolic blood pressure',
            'fetal_heart_rate' => 'fetal heart rate',
            'fundal_height_cm' => 'fundal height',
            'baby_movement' => 'baby movement',
            'next_visit_date' => 'next visit date',
            'next_visit_time' => 'next visit time',
            'conducted_by' => 'conducted by',
        ];
    }
}
