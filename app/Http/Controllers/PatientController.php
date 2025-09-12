<?php
// app/Http/Controllers/PatientController.php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Notifications\HealthcareNotification;
use Illuminate\Support\Facades\Cache;

class PatientController extends Controller
{
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

        $patients = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

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

    // Store new patient
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:15|max:50',
            'contact' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'occupation' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $patient = Patient::create($request->only([
                'name', 'age', 'contact', 'emergency_contact', 'address', 'occupation'
            ]));

            // Send notification to all healthcare workers about new patient registration
            $this->notifyHealthcareWorkers(
                'New Patient Registered',
                "A new patient '{$patient->name}' has been registered in the system.",
                'success',
                Auth::user()->role === 'midwife' 
                    ? route('midwife.patients.show', $patient->id)
                    : route('bhw.patients.show', $patient->id),
                ['patient_id' => $patient->id, 'action' => 'patient_registered']
            );

            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.patients.index' 
                : 'bhw.patients.index';
                
            return redirect()->route($redirectRoute)
                ->with('success', 'Patient registered successfully!');

        } catch (\Exception $e) {
            \Log::error('Error creating patient: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating patient record.')
                ->withInput();
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

    // Update patient
    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:15|max:50',
            'contact' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'occupation' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $patient->update($request->only([
                'name', 'age', 'contact', 'emergency_contact', 'address', 'occupation'
            ]));

            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.patients.index' 
                : 'bhw.patients.index';
                
            return redirect()->route($redirectRoute)
                ->with('success', 'Patient updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Error updating patient: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating patient record.')
                ->withInput();
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

            $patient->delete();

            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.patients.index' 
                : 'bhw.patients.index';
                
            return redirect()->route($redirectRoute)
                ->with('success', 'Patient deleted successfully.');

        } catch (\Exception $e) {
            \Log::error('Error deleting patient: ' . $e->getMessage());
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
     * Helper method to notify all healthcare workers about healthcare events
     */
    private function notifyHealthcareWorkers($title, $message, $type = 'info', $actionUrl = null, $data = [])
    {
        // Get all healthcare workers (midwives and BHWs)
        $healthcareWorkers = User::whereIn('role', ['midwife', 'bhw'])
            ->where('id', '!=', Auth::id()) // Exclude the current user
            ->get();

        foreach ($healthcareWorkers as $worker) {
            $worker->notify(new HealthcareNotification(
                $title,
                $message,
                $type,
                $actionUrl,
                array_merge($data, ['notified_by' => Auth::user()->name])
            ));
            
            // Clear notification cache for the recipient
            Cache::forget("unread_notifications_count_{$worker->id}");
            Cache::forget("recent_notifications_{$worker->id}");
        }
    }
}