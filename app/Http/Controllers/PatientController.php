<?php
// app/Http/Controllers/PatientController.php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Notifications\HealthcareNotification;
use Illuminate\Support\Facades\Cache;
use App\Traits\NotifiesHealthcareWorkers;

class PatientController extends Controller
{
    use NotifiesHealthcareWorkers;
    // Display a listing of patients (mothers only)
    public function index(Request $request)
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        $query = Patient::query();

        // Search functionality
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%");
        }

        $patients = $query->with('activePrenatalRecord')->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.patients.index' 
            : 'bhw.patients.index';
            
        return view($view, compact('patients'));
    }

    // Show form to create new patient
    public function create()
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.patients.create' 
            : 'bhw.patients.create';
            
        return view($view);
    }

    // Store new patient with comprehensive validation
    public function store(Request $request)
    {
        try {
            // Define comprehensive validation rules
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:100',
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
            ], [
                // Custom error messages
                'name.required' => 'Patient name is required.',
                'name.min' => 'Patient name must be at least 2 characters.',
                'name.max' => 'Patient name cannot exceed 100 characters.',
                'name.regex' => 'Patient name should only contain letters, spaces, dots, hyphens, and apostrophes.',
                
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
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please correct the validation errors.',
                        'errors' => $validator->errors()
                    ], 422);
                }

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Please correct the validation errors and try again.');
            }

            // Additional business logic validations
            $validatedData = $validator->validated();
            
            // Check for duplicate patient (same name and age combination)
            $existingPatient = Patient::where('name', 'LIKE', $validatedData['name'])
                ->where('age', $validatedData['age'])
                ->first();
                
            if ($existingPatient) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A patient with the same name and age already exists.',
                        'errors' => ['name' => ['A patient with the same name and age already exists.']]
                    ], 422);
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', 'A patient with the same name and age already exists.');
            }

            // Format phone numbers if provided
            if (!empty($validatedData['contact'])) {
                $validatedData['contact'] = $this->formatPhoneNumber($validatedData['contact']);
            }
            
            if (!empty($validatedData['emergency_contact'])) {
                $validatedData['emergency_contact'] = $this->formatPhoneNumber($validatedData['emergency_contact']);
            }

            // Create the patient record
            $patient = Patient::create($validatedData);

            // Send notification to all healthcare workers about new patient registration
            // If BHW is registering, send high-priority notification to midwives
            if (Auth::user()->role === 'bhw') {
                $this->notifyMidwivesOfBHWAction(
                    'New Patient Registered',
                    "registered a new patient '{$patient->name}' in the system.",
                    'success',
                    route('midwife.patients.show', $patient->id),
                    ['patient_id' => $patient->id, 'action' => 'patient_registered', 'patient_name' => $patient->name]
                );
            }

            // Also send regular notification to all healthcare workers
            $this->notifyHealthcareWorkers(
                'New Patient Registered',
                "A new patient '{$patient->name}' has been registered in the system.",
                'success',
                Auth::user()->role === 'midwife'
                    ? route('midwife.patients.show', $patient->id)
                    : route('bhw.patients.show', $patient->id),
                ['patient_id' => $patient->id, 'action' => 'patient_registered']
            );

            // Success response
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Patient "' . $patient->name . '" has been registered successfully!',
                    'patient' => $patient
                ]);
            }

            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.patients.index' 
                : 'bhw.patients.index';
                
            return redirect()->route($redirectRoute)
                ->with('success', 'Patient "' . $patient->name . '" has been registered successfully!');

        } catch (\Exception $e) {
            // Log the error
            Log::error('Patient registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['_token'])
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred. Please try again.',
                    'errors' => []
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    // Show a single patient
    public function show($id)
    {
        $patient = Patient::with(['prenatalRecords'])->findOrFail($id);
        
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.patients.show' 
            : 'bhw.patients.show';
            
        return view($view, compact('patient'));
    }

    // Show form to edit patient
    public function edit($id)
    {
        $patient = Patient::findOrFail($id);
        
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.patients.edit' 
            : 'bhw.patients.edit';
            
        return view($view, compact('patient'));
    }

    // Update patient with comprehensive validation
    public function update(Request $request, $id)
    {
        try {
            $patient = Patient::findOrFail($id);

            // Define comprehensive validation rules (same as store)
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:100',
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
            ], [
                // Same custom error messages as store method
                'name.required' => 'Patient name is required.',
                'name.min' => 'Patient name must be at least 2 characters.',
                'name.max' => 'Patient name cannot exceed 100 characters.',
                'name.regex' => 'Patient name should only contain letters, spaces, dots, hyphens, and apostrophes.',
                
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
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please correct the validation errors.',
                        'errors' => $validator->errors()
                    ], 422);
                }

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Please correct the validation errors and try again.');
            }

            // Additional business logic validations
            $validatedData = $validator->validated();
            
            // Check for duplicate patient (excluding current patient)
            $existingPatient = Patient::where('name', 'LIKE', $validatedData['name'])
                ->where('age', $validatedData['age'])
                ->where('id', '!=', $patient->id)
                ->first();
                
            if ($existingPatient) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Another patient with the same name and age already exists.',
                        'errors' => ['name' => ['Another patient with the same name and age already exists.']]
                    ], 422);
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Another patient with the same name and age already exists.');
            }

            // Format phone numbers if provided
            if (!empty($validatedData['contact'])) {
                $validatedData['contact'] = $this->formatPhoneNumber($validatedData['contact']);
            }
            
            if (!empty($validatedData['emergency_contact'])) {
                $validatedData['emergency_contact'] = $this->formatPhoneNumber($validatedData['emergency_contact']);
            }

            // Update the patient record
            $patient->update($validatedData);

            // Success response
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Patient "' . $patient->name . '" has been updated successfully!',
                    'patient' => $patient
                ]);
            }

            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.patients.index' 
                : 'bhw.patients.index';
                
            return redirect()->route($redirectRoute)
                ->with('success', 'Patient "' . $patient->name . '" has been updated successfully!');

        } catch (\Exception $e) {
            // Log the error
            Log::error('Patient update failed', [
                'patient_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['_token'])
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred. Please try again.',
                    'errors' => []
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    // Delete patient (only if no prenatal records)
    public function destroy($id)
    {
        try {
            $patient = Patient::with('prenatalRecords')->findOrFail($id);

            if ($patient->prenatalRecords()->count() > 0) {
                $redirectRoute = Auth::user()->role === 'midwife' 
                    ? 'midwife.patients.index' 
                    : 'bhw.patients.index';
                    
                return redirect()->route($redirectRoute)
                    ->with('error', 'Cannot delete patient with existing prenatal records.');
            }

            $patientName = $patient->name;
            $patient->delete();

            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.patients.index' 
                : 'bhw.patients.index';
                
            return redirect()->route($redirectRoute)
                ->with('success', "Patient \"{$patientName}\" has been deleted successfully.");

        } catch (\Exception $e) {
            Log::error('Error deleting patient: ' . $e->getMessage());
            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.patients.index' 
                : 'bhw.patients.index';
                
            return redirect()->route($redirectRoute)
                ->with('error', 'Error deleting patient. Please try again.');
        }
    }

    // Search patients for dropdown (AJAX)
    public function search(Request $request)
    {
        $term = $request->get('term', '');
        $patients = Patient::where('name', 'LIKE', "%{$term}%")
                           ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%")
                           ->limit(10)
                           ->get(['id', 'name', 'formatted_patient_id', 'age']);
                           
        return response()->json($patients);
    }

    /**
     * Format phone number to consistent format
     */
    private function formatPhoneNumber($phone)
    {
        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);
        
        // Convert to +63 format
        if (substr($digits, 0, 2) === '63') {
            return '+' . $digits;
        } elseif (substr($digits, 0, 1) === '0') {
            return '+63' . substr($digits, 1);
        } elseif (strlen($digits) === 10) {
            return '+63' . $digits;
        }
        
        return $phone; // Return original if can't format
    }

}