<?php

namespace App\Http\Controllers;

use App\Models\PrenatalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Notifications\HealthcareNotification;
use Illuminate\Support\Facades\Cache;

class PrenatalRecordController extends Controller
{
    // Display a listing of prenatal records
    public function index(Request $request)
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        $query = PrenatalRecord::with(['patient'])->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('patient', function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $prenatalRecords = $query->paginate(20)->withQueryString();

        // Get patients for the modal dropdown
        $patients = Patient::orderBy('name')->get(['id', 'name', 'formatted_patient_id', 'age']);
        
        // Get options for dropdowns
        $gravida_options = [1 => 'G1', 2 => 'G2', 3 => 'G3', 4 => 'G4', 5 => 'G5+'];
        $para_options = [0 => 'P0', 1 => 'P1', 2 => 'P2', 3 => 'P3', 4 => 'P4+'];

        // Return appropriate view based on user role
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalrecord.index' 
            : 'bhw.prenatalrecord.index';
            
        return view($view, compact('prenatalRecords', 'patients', 'gravida_options', 'para_options'));
    }

    // Show form to create new prenatal record
    public function create()
    {
        $gravida_options = [1 => 'G1', 2 => 'G2', 3 => 'G3', 4 => 'G4', 5 => 'G5+'];
        $para_options = [0 => 'P0', 1 => 'P1', 2 => 'P2', 3 => 'P3', 4 => 'P4+'];
        
        // Get all patients for the dropdown
        $patients = Patient::orderBy('name')->get(['id', 'name', 'formatted_patient_id', 'age']);
        
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalrecord.create' 
            : 'bhw.prenatalrecord.create';
            
        return view($view, compact('gravida_options', 'para_options', 'patients'));
    }

    // Store new prenatal record
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'last_menstrual_period' => 'required|date|before_or_equal:today',
            'expected_due_date' => 'nullable|date|after:last_menstrual_period',
            'gravida' => 'nullable|integer|min:1|max:10',
            'para' => 'nullable|integer|min:0|max:10',
            'medical_history' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:500',
            'blood_pressure' => 'nullable|string|max:20',
            'weight' => 'nullable|numeric|min:30|max:200',
            'height' => 'nullable|numeric|min:120|max:200',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if patient already has an active prenatal record
        $existingRecord = PrenatalRecord::where('patient_id', $request->patient_id)
                                       ->whereIn('status', ['normal', 'monitor', 'high-risk', 'due'])
                                       ->first();

        if ($existingRecord) {
            return redirect()->back()
                ->with('error', 'This patient already has an active prenatal record.')
                ->withInput();
        }

        try {
            $lmp = Carbon::parse($request->last_menstrual_period);
            
            // Calculate gestational age in weeks and days format
            $totalDays = $lmp->diffInDays(now());
            $weeks = intval($totalDays / 7);
            $days = $totalDays % 7;
            
            // Format gestational age
            if ($weeks == 0) {
                $gestationalAge = $days == 1 ? "1 day" : "{$days} days";
            } elseif ($days == 0) {
                $gestationalAge = $weeks == 1 ? "1 week" : "{$weeks} weeks";
            } else {
                $weekText = $weeks == 1 ? "1 week" : "{$weeks} weeks";
                $dayText = $days == 1 ? "1 day" : "{$days} days";
                $gestationalAge = "{$weekText} {$dayText}";
            }

            $prenatalRecord = PrenatalRecord::create([
                'patient_id' => $request->patient_id,
                'last_menstrual_period' => $request->last_menstrual_period,
                'expected_due_date' => $request->expected_due_date ?? $lmp->copy()->addDays(280)->toDateString(),
                'gestational_age' => $gestationalAge,
                'trimester' => $weeks <= 12 ? 1 : ($weeks <= 26 ? 2 : 3),
                'gravida' => $request->gravida,
                'para' => $request->para,
                'medical_history' => $request->medical_history,
                'notes' => $request->notes,
                'blood_pressure' => $request->blood_pressure,
                'weight' => $request->weight,
                'height' => $request->height,
                'status' => 'normal',
            ]);

            // Get patient name for notification
            $patient = Patient::find($request->patient_id);
            
            // Send notification to all healthcare workers about new prenatal record
            $this->notifyHealthcareWorkers(
                'New Prenatal Record Created',
                "A new prenatal record has been created for patient '{$patient->name}' (Gestational Age: {$gestationalAge}).",
                'info',
                Auth::user()->role === 'midwife' 
                    ? route('midwife.prenatalrecord.show', $prenatalRecord->id)
                    : route('bhw.prenatalrecord.show', $prenatalRecord->id),
                ['prenatal_record_id' => $prenatalRecord->id, 'patient_id' => $patient->id, 'action' => 'prenatal_record_created']
            );

            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.prenatalrecord.index' 
                : 'bhw.prenatalrecord.index';
                
            return redirect()->route($redirectRoute)
                ->with('success', 'Prenatal record created successfully!');

        } catch (\Exception $e) {
            \Log::error('Error creating prenatal record: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating record.')
                ->withInput();
        }
    }

    // Show a single prenatal record
    public function show($id)
    {
        $prenatalRecord = PrenatalRecord::with([
            'patient.prenatalCheckups' => function($query) {
                $query->orderBy('checkup_date', 'desc');
            },
            'patient.latestCheckup'
        ])->findOrFail($id);
        
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalrecord.show' 
            : 'bhw.prenatalrecord.show';
            
        return view($view, compact('prenatalRecord'));
    }

    // Show form to edit prenatal record
    public function edit($id)
    {
        $prenatal = PrenatalRecord::with('patient')->findOrFail($id);
        $gravida_options = [1 => 'G1', 2 => 'G2', 3 => 'G3', 4 => 'G4', 5 => 'G5+'];
        $para_options = [0 => 'P0', 1 => 'P1', 2 => 'P2', 3 => 'P3', 4 => 'P4+'];
        
        // Get all patients for the dropdown (in case they want to reassign)
        $patients = Patient::orderBy('name')->get(['id', 'name', 'formatted_patient_id', 'age']);
        
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalrecord.edit' 
            : 'bhw.prenatalrecord.edit';
            
        return view($view, compact('prenatal', 'gravida_options', 'para_options', 'patients'));
    }

    // Update prenatal record
    public function update(Request $request, $id)
    {
        $prenatal = PrenatalRecord::with('patient')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'last_menstrual_period' => 'required|date|before_or_equal:today',
            'expected_due_date' => 'nullable|date|after:last_menstrual_period',
            'gravida' => 'nullable|integer|min:1|max:10',
            'para' => 'nullable|integer|min:0|max:10',
            'medical_history' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:500',
            'status' => 'nullable|in:normal,monitor,high-risk,due,completed',
            'blood_pressure' => 'nullable|string|max:20',
            'weight' => 'nullable|numeric|min:30|max:200',
            'height' => 'nullable|numeric|min:120|max:200',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // If changing patient, check if the new patient already has an active record
        if ($request->patient_id != $prenatal->patient_id) {
            $existingRecord = PrenatalRecord::where('patient_id', $request->patient_id)
                                           ->whereIn('status', ['normal', 'monitor', 'high-risk', 'due'])
                                           ->where('id', '!=', $id)
                                           ->first();

            if ($existingRecord) {
                return redirect()->back()
                    ->with('error', 'The selected patient already has an active prenatal record.')
                    ->withInput();
            }
        }

        try {
            // Calculate gestational age in weeks and days format
            $lmp = Carbon::parse($request->last_menstrual_period);
            $totalDays = $lmp->diffInDays(now());
            $weeks = intval($totalDays / 7);
            $days = $totalDays % 7;
            
            // Format gestational age
            if ($weeks == 0) {
                $gestationalAge = $days == 1 ? "1 day" : "{$days} days";
            } elseif ($days == 0) {
                $gestationalAge = $weeks == 1 ? "1 week" : "{$weeks} weeks";
            } else {
                $weekText = $weeks == 1 ? "1 week" : "{$weeks} weeks";
                $dayText = $days == 1 ? "1 day" : "{$days} days";
                $gestationalAge = "{$weekText} {$dayText}";
            }

            // Update prenatal record
            $prenatal->update([
                'patient_id' => $request->patient_id,
                'last_menstrual_period' => $request->last_menstrual_period,
                'expected_due_date' => $request->expected_due_date ?? $lmp->copy()->addDays(280)->toDateString(),
                'gestational_age' => $gestationalAge,
                'trimester' => $weeks <= 12 ? 1 : ($weeks <= 26 ? 2 : 3),
                'gravida' => $request->gravida,
                'para' => $request->para,
                'medical_history' => $request->medical_history,
                'notes' => $request->notes,
                'status' => $request->status ?? 'normal',
                'blood_pressure' => $request->blood_pressure,
                'weight' => $request->weight,
                'height' => $request->height,
            ]);

            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.prenatalrecord.index' 
                : 'bhw.prenatalrecord.index';
                
            return redirect()->route($redirectRoute)
                ->with('success', 'Prenatal record updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Error updating prenatal record: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating record.')
                ->withInput();
        }
    }

    // Delete prenatal record
    public function destroy($id)
    {
        try {
            $prenatal = PrenatalRecord::findOrFail($id);
            $prenatal->delete();

            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.prenatalrecord.index' 
                : 'bhw.prenatalrecord.index';
                
            return redirect()->route($redirectRoute)
                ->with('success', 'Prenatal record deleted successfully.');

        } catch (\Exception $e) {
            \Log::error('Error deleting prenatal record: ' . $e->getMessage());
            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.prenatalrecord.index' 
                : 'bhw.prenatalrecord.index';
                
            return redirect()->route($redirectRoute)
                ->with('error', 'Error deleting record. Please try again.');
        }
    }

    // AJAX endpoint to get record data for editing
    public function getRecordData($id)
    {
        try {
            $record = PrenatalRecord::with('patient')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $record
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found'
            ], 404);
        }
    }

    // Get patients for AJAX dropdown (keep this for Select2 search)
    public function getPatients(Request $request)
    {
        $term = $request->get('q', ''); // Select2 uses 'q' parameter
        
        $patients = Patient::where('name', 'LIKE', "%{$term}%")
                           ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%")
                           ->orderBy('name')
                           ->limit(10)
                           ->get(['id', 'name', 'formatted_patient_id', 'age']);
                           
        return response()->json([
            'results' => $patients->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'text' => $patient->name . ' (' . $patient->formatted_patient_id . ') - ' . $patient->age . ' years'
                ];
            })
        ]);
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