<?php

namespace App\Http\Controllers;

use App\Models\PrenatalCheckup;
use App\Models\PrenatalRecord;
use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Notifications\HealthcareNotification;
use Illuminate\Support\Facades\Cache;

class PrenatalCheckupController extends Controller
{
    // Display a listing of prenatal checkups
    public function index(Request $request)
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        $query = PrenatalCheckup::with(['prenatalRecord.patient'])->orderBy('checkup_date', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('prenatalRecord.patient', function ($q) use ($term) {
                $q->where('first_name', 'LIKE', "%{$term}%")
                  ->orWhere('last_name', 'LIKE', "%{$term}%")
                  ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('checkup_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('checkup_date', '<=', $request->date_to);
        }

        $checkups = $query->paginate(20)->withQueryString();

        // Get patients with active prenatal records for the view
        $patients = Patient::with(['prenatalRecords' => function($query) {
            $query->where('is_active', true);
        }, 'prenatalCheckups' => function($query) {
            $query->orderBy('checkup_date', 'desc');
        }])->whereHas('prenatalRecords', function($query) {
            $query->where('is_active', true);
        })->get();

        // Get prenatal records for the modal dropdown
        $prenatalRecords = PrenatalRecord::with('patient')->where('is_active', true)->get();
        
        // Get users for conducted_by dropdown
        $healthcareWorkers = User::whereIn('role', ['midwife', 'bhw'])->orderBy('name')->get();

        // Return appropriate view based on user role
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalcheckup.index' 
            : 'bhw.prenatalcheckup.index';
            
        return view($view, compact('checkups', 'patients', 'prenatalRecords', 'healthcareWorkers'));
    }

    // Show form to create new prenatal checkup
    public function create()
    {
        // Get active prenatal records
        $prenatalRecords = PrenatalRecord::with('patient')->where('is_active', true)->get();
        
        // Get healthcare workers
        $healthcareWorkers = User::whereIn('role', ['midwife', 'bhw'])->orderBy('name')->get();
        
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalcheckup.create' 
            : 'bhw.prenatalcheckup.create';
            
        return view($view, compact('prenatalRecords', 'healthcareWorkers'));
    }

    // Store new prenatal checkup
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'checkup_date' => 'required|date',
            'checkup_time' => 'required|date_format:H:i',
            'gestational_age_weeks' => 'nullable|integer|min:1|max:45',
            'weight_kg' => 'nullable|numeric|min:30|max:200',
            'blood_pressure_systolic' => 'nullable|integer|min:70|max:250',
            'blood_pressure_diastolic' => 'nullable|integer|min:40|max:150',
            'fetal_heart_rate' => 'nullable|integer|min:100|max:180',
            'fundal_height_cm' => 'nullable|numeric|min:10|max:50',
            'presentation' => 'nullable|string|max:50',
            'symptoms' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'next_visit_date' => 'nullable|date|after:checkup_date',
            'next_visit_time' => 'nullable|date_format:H:i',
            'next_visit_notes' => 'nullable|string|max:500',
            'conducted_by' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Get patient and their active prenatal record
            $patient = Patient::findOrFail($request->patient_id);
            $prenatalRecord = $patient->prenatalRecords()->where('is_active', true)->first();

            // Determine status based on whether medical data is provided
            $hasmedicalData = $request->weight_kg || $request->blood_pressure_systolic ||
                             $request->fetal_heart_rate || $request->fundal_height_cm ||
                             $request->symptoms || $request->notes;

            $status = $hasmedicalData ? 'done' : 'upcoming';

            // Create prenatal checkup directly (simplified - no complex appointment linking)
            $checkup = PrenatalCheckup::create([
                'patient_id' => $request->patient_id,
                'prenatal_record_id' => $prenatalRecord ? $prenatalRecord->id : null,
                'checkup_date' => $request->checkup_date,
                'checkup_time' => $request->checkup_time,
                'gestational_age_weeks' => $request->gestational_age_weeks,
                'weight_kg' => $request->weight_kg,
                'blood_pressure_systolic' => $request->blood_pressure_systolic,
                'blood_pressure_diastolic' => $request->blood_pressure_diastolic,
                'fetal_heart_rate' => $request->fetal_heart_rate,
                'fundal_height_cm' => $request->fundal_height_cm,
                'symptoms' => $request->symptoms,
                'notes' => $request->notes,
                'status' => $status,
                'next_visit_date' => $request->next_visit_date,
                'next_visit_time' => $request->next_visit_time,
                'next_visit_notes' => $request->next_visit_notes,
                'conducted_by' => $request->conducted_by ?? Auth::id(),
                // Legacy fields for backward compatibility
                'bp_high' => $request->blood_pressure_systolic,
                'bp_low' => $request->blood_pressure_diastolic,
                'weight' => $request->weight_kg,
                'baby_heartbeat' => $request->fetal_heart_rate,
                'belly_size' => $request->fundal_height_cm,
            ]);

            // If scheduling next visit, create another upcoming checkup
            if ($request->next_visit_date && $request->schedule_next) {
                PrenatalCheckup::create([
                    'patient_id' => $request->patient_id,
                    'prenatal_record_id' => $prenatalRecord ? $prenatalRecord->id : null,
                    'checkup_date' => $request->next_visit_date,
                    'checkup_time' => $request->next_visit_time ?? '09:00',
                    'status' => 'upcoming',
                    'notes' => $request->next_visit_notes,
                    'conducted_by' => Auth::id(),
                ]);
            }

            // Send notification to all healthcare workers about new checkup
            $statusMessage = $status === 'done' ? 'completed' : 'scheduled';
            $this->notifyHealthcareWorkers(
                "Prenatal Checkup {$statusMessage}",
                "A prenatal checkup has been {$statusMessage} for patient '{$patient->first_name} {$patient->last_name}' on " . Carbon::parse($request->checkup_date)->format('M d, Y'),
                'info',
                Auth::user()->role === 'midwife'
                    ? route('midwife.prenatalcheckup.show', $checkup->id)
                    : route('bhw.prenatalcheckup.show', $checkup->id),
                ['checkup_id' => $checkup->id, 'prenatal_record_id' => $prenatalRecord ? $prenatalRecord->id : null, 'action' => 'checkup_created']
            );

            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.prenatalcheckup.index' 
                : 'bhw.prenatalcheckup.index';
                
            $successMessage = $status === 'done'
                ? 'Prenatal checkup completed and recorded successfully!'
                : 'Prenatal checkup scheduled successfully!';

            return redirect()->route($redirectRoute)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            \Log::error('Error creating prenatal checkup: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating checkup.')
                ->withInput();
        }
    }

    // Show a single prenatal checkup
    public function show($id)
    {
        $checkup = PrenatalCheckup::with([
            'prenatalRecord.patient',
            'conductedBy'
        ])->findOrFail($id);
        
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalcheckup.show' 
            : 'bhw.prenatalcheckup.show';
            
        return view($view, compact('checkup'));
    }

    // Show form to edit prenatal checkup
    public function edit($id)
    {
        $checkup = PrenatalCheckup::with('prenatalRecord.patient')->findOrFail($id);
        
        // Get active prenatal records
        $prenatalRecords = PrenatalRecord::with('patient')->where('is_active', true)->get();
        
        // Get healthcare workers
        $healthcareWorkers = User::whereIn('role', ['midwife', 'bhw'])->orderBy('name')->get();
        
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalcheckup.edit' 
            : 'bhw.prenatalcheckup.edit';
            
        return view($view, compact('checkup', 'prenatalRecords', 'healthcareWorkers'));
    }

    // Update prenatal checkup
    public function update(Request $request, $id)
    {
        $checkup = PrenatalCheckup::with('prenatalRecord.patient')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'prenatal_record_id' => 'required|exists:prenatal_records,id',
            'checkup_date' => 'required|date',
            'gestational_age_weeks' => 'nullable|integer|min:1|max:45',
            'weight_kg' => 'nullable|numeric|min:30|max:200',
            'blood_pressure_systolic' => 'nullable|integer|min:70|max:250',
            'blood_pressure_diastolic' => 'nullable|integer|min:40|max:150',
            'fetal_heart_rate' => 'nullable|integer|min:100|max:180',
            'fundal_height_cm' => 'nullable|numeric|min:10|max:50',
            'presentation' => 'nullable|string|max:50',
            'symptoms' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'next_visit_date' => 'nullable|date|after:checkup_date',
            'conducted_by' => 'nullable|exists:users,id',
            'status' => 'required|in:scheduled,completed,cancelled,rescheduled',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $oldStatus = $checkup->status;
            
            // Update checkup
            $checkup->update([
                'prenatal_record_id' => $request->prenatal_record_id,
                'checkup_date' => $request->checkup_date,
                'gestational_age_weeks' => $request->gestational_age_weeks,
                'weight_kg' => $request->weight_kg,
                'blood_pressure_systolic' => $request->blood_pressure_systolic,
                'blood_pressure_diastolic' => $request->blood_pressure_diastolic,
                'fetal_heart_rate' => $request->fetal_heart_rate,
                'fundal_height_cm' => $request->fundal_height_cm,
                'presentation' => $request->presentation,
                'symptoms' => $request->symptoms,
                'notes' => $request->notes,
                'next_visit_date' => $request->next_visit_date,
                'conducted_by' => $request->conducted_by,
                'status' => $request->status,
            ]);

            // Send notification if status changed significantly
            if ($oldStatus !== $request->status && in_array($request->status, ['completed', 'cancelled', 'rescheduled'])) {
                $statusMessages = [
                    'completed' => 'completed',
                    'cancelled' => 'cancelled',
                    'rescheduled' => 'rescheduled'
                ];
                
                $this->notifyHealthcareWorkers(
                    'Prenatal Checkup Status Updated',
                    "Prenatal checkup for patient '{$checkup->prenatalRecord->patient->first_name} {$checkup->prenatalRecord->patient->last_name}' has been {$statusMessages[$request->status]}.",
                    'info',
                    Auth::user()->role === 'midwife' 
                        ? route('midwife.prenatalcheckup.show', $checkup->id)
                        : route('bhw.prenatalcheckup.show', $checkup->id),
                    ['checkup_id' => $checkup->id, 'action' => 'checkup_updated']
                );
            }

            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.prenatalcheckup.index' 
                : 'bhw.prenatalcheckup.index';
                
            return redirect()->route($redirectRoute)
                ->with('success', 'Prenatal checkup updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Error updating prenatal checkup: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating checkup.')
                ->withInput();
        }
    }

    // Delete prenatal checkup
    public function destroy($id)
    {
        try {
            $checkup = PrenatalCheckup::with('prenatalRecord.patient')->findOrFail($id);
            $patientName = $checkup->prenatalRecord->patient->first_name . ' ' . $checkup->prenatalRecord->patient->last_name;
            
            $checkup->delete();

            // Notify about deletion
            $this->notifyHealthcareWorkers(
                'Prenatal Checkup Deleted',
                "A prenatal checkup for patient '{$patientName}' has been deleted.",
                'warning',
                null,
                ['action' => 'checkup_deleted']
            );

            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.prenatalcheckup.index' 
                : 'bhw.prenatalcheckup.index';
                
            return redirect()->route($redirectRoute)
                ->with('success', 'Prenatal checkup deleted successfully.');

        } catch (\Exception $e) {
            \Log::error('Error deleting prenatal checkup: ' . $e->getMessage());
            $redirectRoute = Auth::user()->role === 'midwife' 
                ? 'midwife.prenatalcheckup.index' 
                : 'bhw.prenatalcheckup.index';
                
            return redirect()->route($redirectRoute)
                ->with('error', 'Error deleting checkup. Please try again.');
        }
    }

    // AJAX endpoint to get checkup data for editing
    public function getCheckupData($id)
    {
        try {
            $checkup = PrenatalCheckup::with('prenatalRecord.patient')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $checkup
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Checkup not found'
            ], 404);
        }
    }

    // AJAX endpoint to get full checkup data for modals
    public function getData($id)
    {
        try {
            $checkup = PrenatalCheckup::with([
                'patient',
                'prenatalRecord.patient',
                'appointment',
                'conductedBy'
            ])->findOrFail($id);

            return response()->json($checkup);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Checkup not found'
            ], 404);
        }
    }

    // Get prenatal records for AJAX dropdown
    public function getPrenatalRecords(Request $request)
    {
        $term = $request->get('q', '');
        
        $records = PrenatalRecord::with('patient')
                                ->where('is_active', true)
                                ->whereHas('patient', function($query) use ($term) {
                                    $query->where('first_name', 'LIKE', "%{$term}%")
                                          ->orWhere('last_name', 'LIKE', "%{$term}%")
                                          ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%");
                                })
                                ->orderBy('created_at', 'desc')
                                ->limit(10)
                                ->get();
                           
        return response()->json([
            'results' => $records->map(function ($record) {
                return [
                    'id' => $record->id,
                    'text' => $record->patient->first_name . ' ' . $record->patient->last_name . ' (' . $record->patient->formatted_patient_id . ') - ' . $record->gestational_age_weeks . ' weeks'
                ];
            })
        ]);
    }

    // Mark checkup as completed (quick action)
    public function markCompleted($id)
    {
        try {
            $checkup = PrenatalCheckup::with('prenatalRecord.patient')->findOrFail($id);
            $checkup->update(['status' => 'completed']);

            $this->notifyHealthcareWorkers(
                'Prenatal Checkup Completed',
                "Prenatal checkup for patient '{$checkup->prenatalRecord->patient->first_name} {$checkup->prenatalRecord->patient->last_name}' has been marked as completed.",
                'success',
                Auth::user()->role === 'midwife' 
                    ? route('midwife.prenatalcheckup.show', $checkup->id)
                    : route('bhw.prenatalcheckup.show', $checkup->id),
                ['checkup_id' => $checkup->id, 'action' => 'checkup_completed']
            );

            return response()->json([
                'success' => true,
                'message' => 'Checkup marked as completed successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error marking checkup as completed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating checkup status.'
            ], 500);
        }
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

    // Update only the schedule/appointment details
    public function updateSchedule(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        $checkup = PrenatalCheckup::with(['prenatalRecord.patient'])->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'next_visit_date' => 'required|date|after:today',
            'next_visit_time' => 'required|date_format:H:i',
            'next_visit_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Update only the scheduling fields
            $checkup->update([
                'next_visit_date' => $request->next_visit_date,
                'next_visit_time' => $request->next_visit_time,
                'next_visit_notes' => $request->next_visit_notes,
            ]);

            // Send notification about schedule update
            $patient = $checkup->prenatalRecord->patient;
            $formattedDate = Carbon::parse($request->next_visit_date)->format('F j, Y');
            $formattedTime = Carbon::parse($request->next_visit_time)->format('g:i A');

            $this->notifyHealthcareWorkers(
                'Appointment Rescheduled',
                "Prenatal checkup appointment for {$patient->first_name} {$patient->last_name} has been rescheduled to {$formattedDate} at {$formattedTime}.",
                'info',
                Auth::user()->role === 'midwife'
                    ? route('midwife.prenatalcheckup.index')
                    : route('bhw.prenatalcheckup.index'),
                [
                    'checkup_id' => $checkup->id,
                    'action' => 'appointment_rescheduled',
                    'patient_name' => "{$patient->first_name} {$patient->last_name}",
                    'new_date' => $formattedDate,
                    'new_time' => $formattedTime
                ]
            );

            return redirect()->back()->with('success', 'Appointment schedule updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Error updating prenatal checkup schedule: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update appointment schedule. Please try again.');
        }
    }
}