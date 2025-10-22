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
use App\Rules\ValidBloodPressure;
use App\Services\PrenatalCheckupService;
use App\Services\SmsService;
use App\Http\Requests\StorePrenatalCheckupRequest;
use App\Http\Requests\UpdatePrenatalCheckupRequest;

class PrenatalCheckupController extends Controller
{
    protected $prenatalCheckupService;

    public function __construct(PrenatalCheckupService $prenatalCheckupService)
    {
        $this->prenatalCheckupService = $prenatalCheckupService;
    }
    // Display a listing of prenatal checkups
    public function index(Request $request)
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        // Auto-check for today's missed checkups (if after business hours)
        $this->checkTodaysMissed();

        // Optimize: Load only necessary relationships (removed redundant 'patient')
        $query = PrenatalCheckup::with(['prenatalRecord.patient'])
            ->where(function($query) {
                // Show all 'done' checkups regardless of pregnancy status (for historical records)
                $query->where('status', 'done')
                      // OR show 'upcoming'/'missed' checkups only for active, non-completed pregnancies
                      ->orWhere(function($subQuery) {
                          $subQuery->whereIn('status', ['upcoming', 'missed'])
                                   ->whereHas('prenatalRecord', function($q) {
                                       $q->where('is_active', 1)
                                         ->where('status', '!=', 'completed');
                                   });
                      });
            })
            ->orderBy('checkup_date', 'desc');

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

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('checkup_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('checkup_date', '<=', $request->date_to);
        }

        $checkups = $query->paginate(20)->withQueryString();

        // Load next upcoming checkups for each patient to avoid N+1 queries
        $patientIds = $checkups->pluck('patient_id')->merge(
            $checkups->where('patient_id', null)->pluck('prenatalRecord.patient.id')
        )->filter()->unique();

        $nextCheckups = collect();
        if ($patientIds->isNotEmpty()) {
            $nextCheckups = PrenatalCheckup::whereIn('patient_id', $patientIds)
                ->where('status', 'upcoming')
                ->where('checkup_date', '>', now())
                ->orderBy('patient_id')
                ->orderBy('checkup_date', 'asc')
                ->get()
                ->groupBy('patient_id')
                ->map(function($checkups) {
                    return $checkups->first(); // Get the earliest upcoming checkup for each patient
                });
        }

        // Optimize: Removed duplicate WHERE clause (was in both with() and whereHas())
        $patients = Patient::whereHas('prenatalRecords', function($query) {
            $query->where('is_active', true)
                  ->where('status', '!=', 'completed');
        })->with(['prenatalRecords' => function($query) {
            $query->where('is_active', true)
                  ->where('status', '!=', 'completed')
                  ->latest();
        }, 'prenatalCheckups' => function($query) {
            $query->orderBy('checkup_date', 'desc')->limit(5); // Limit to recent checkups only
        }])->get();

        // Get prenatal records for the modal dropdown (exclude completed pregnancies)
        $prenatalRecords = PrenatalRecord::with('patient')
            ->where('is_active', true)
            ->where('status', '!=', 'completed')
            ->get();
        
        // Get users for conducted_by dropdown
        $healthcareWorkers = User::whereIn('role', ['midwife', 'bhw'])->orderBy('name')->get();

        // Return appropriate view based on user role
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalcheckup.index' 
            : 'bhw.prenatalcheckup.index';
            
        return view($view, compact('checkups', 'patients', 'prenatalRecords', 'healthcareWorkers', 'nextCheckups'));
    }

    // Show form to create new prenatal checkup
    public function create()
    {
        // Get active prenatal records (exclude completed pregnancies)
        $prenatalRecords = PrenatalRecord::with('patient')
            ->where('is_active', true)
            ->where('status', '!=', 'completed')
            ->get();
        
        // Get healthcare workers
        $healthcareWorkers = User::whereIn('role', ['midwife', 'bhw'])->orderBy('name')->get();
        
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalcheckup.create' 
            : 'bhw.prenatalcheckup.create';
            
        return view($view, compact('prenatalRecords', 'healthcareWorkers'));
    }

    // Store new prenatal checkup
    public function store(StorePrenatalCheckupRequest $request)
    {
        // Check for duplicate completed checkups
        if ($this->prenatalCheckupService->checkupExists($request->patient_id, $request->checkup_date)) {
            return redirect()->back()
                ->withErrors(['checkup_date' => 'A completed prenatal checkup already exists for this patient on the selected date.'])
                ->withInput();
        }

        // Check blood pressure and add warning if needed
        $bpWarning = null;
        if ($request->blood_pressure_systolic && $request->blood_pressure_diastolic) {
            $bpWarning = ValidBloodPressure::getWarningLevel(
                $request->blood_pressure_systolic,
                $request->blood_pressure_diastolic
            );
        }

        try {
            // Create checkup using service
            $checkup = $this->prenatalCheckupService->createCheckup($request->validated());

            // Get patient for notification
            $patient = Patient::findOrFail($request->patient_id);

            // Send notification to all healthcare workers
            $statusMessage = $checkup->status === 'done' ? 'completed' : 'scheduled';

            $this->notifyHealthcareWorkers(
                "Prenatal Checkup {$statusMessage}",
                "A prenatal checkup has been {$statusMessage} for patient '{$patient->first_name} {$patient->last_name}' on " . Carbon::parse($request->checkup_date)->format('M d, Y'),
                'info',
                Auth::user()->role === 'midwife'
                    ? route('midwife.prenatalcheckup.show', $checkup->id)
                    : route('bhw.prenatalcheckup.show', $checkup->id),
                ['checkup_id' => $checkup->id, 'action' => 'checkup_created']
            );

            // Prepare redirect with success message
            $redirectRoute = Auth::user()->role === 'midwife'
                ? 'midwife.prenatalcheckup.index'
                : 'bhw.prenatalcheckup.index';

            $successMessage = $checkup->status === 'done'
                ? 'Prenatal checkup completed and recorded successfully!'
                : 'Prenatal checkup scheduled successfully!';

            $redirect = redirect()->route($redirectRoute)->with('success', $successMessage);

            // Add blood pressure warning if exists
            if ($bpWarning) {
                $redirect->with($bpWarning['level'], $bpWarning['message']);
            }

            return $redirect;

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
    public function update(UpdatePrenatalCheckupRequest $request, $id)
    {
        $checkup = PrenatalCheckup::with('prenatalRecord.patient')->findOrFail($id);

        // Check for duplicate completed checkups (excluding current)
        if ($this->prenatalCheckupService->checkupExists($checkup->patient_id, $request->checkup_date, $id)) {
            return redirect()->back()
                ->withErrors(['checkup_date' => 'A completed prenatal checkup already exists for this patient on the selected date.'])
                ->withInput();
        }

        // Check blood pressure and add warning if needed
        $bpWarning = null;
        if ($request->blood_pressure_systolic && $request->blood_pressure_diastolic) {
            $bpWarning = ValidBloodPressure::getWarningLevel(
                $request->blood_pressure_systolic,
                $request->blood_pressure_diastolic
            );
        }

        try {
            $oldStatus = $checkup->status;

            // Update checkup using service
            $this->prenatalCheckupService->updateCheckup($checkup, $request->validated());

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

            // Prepare redirect
            $redirectRoute = Auth::user()->role === 'midwife'
                ? 'midwife.prenatalcheckup.index'
                : 'bhw.prenatalcheckup.index';

            $redirect = redirect()->route($redirectRoute)->with('success', 'Prenatal checkup updated successfully!');

            // Add blood pressure warning if exists
            if ($bpWarning) {
                $redirect->with($bpWarning['level'], $bpWarning['message']);
            }

            return $redirect;

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

            // Get the patient ID from the checkup
            $patientId = $checkup->patient_id ?? ($checkup->prenatalRecord->patient_id ?? null);

            // Load the next upcoming checkup for this patient
            $nextCheckup = null;
            if ($patientId) {
                $nextCheckup = PrenatalCheckup::where('patient_id', $patientId)
                    ->where('status', 'upcoming')
                    ->where('checkup_date', '>', now())
                    ->orderBy('checkup_date', 'asc')
                    ->first();
            }

            // Convert checkup to array and add next checkup data
            $checkupData = $checkup->toArray();
            $checkupData['next_checkup'] = $nextCheckup;

            // Add formatted dates for current checkup
            $checkupData['formatted_checkup_date'] = $checkup->checkup_date ? \Carbon\Carbon::parse($checkup->checkup_date)->format('M d, Y') : null;
            $checkupData['formatted_checkup_time'] = $checkup->checkup_time ? \Carbon\Carbon::parse($checkup->checkup_time)->format('g:i A') : null;
            $checkupData['formatted_next_visit_date'] = $checkup->next_visit_date ? \Carbon\Carbon::parse($checkup->next_visit_date)->format('M d, Y') : null;
            $checkupData['formatted_next_visit_time'] = $checkup->next_visit_time ? \Carbon\Carbon::parse($checkup->next_visit_time)->format('g:i A') : null;

            // Add formatted dates for next checkup
            if ($nextCheckup) {
                $checkupData['next_checkup']['formatted_checkup_date'] = \Carbon\Carbon::parse($nextCheckup->checkup_date)->format('M d, Y');
                $checkupData['next_checkup']['formatted_checkup_time'] = $nextCheckup->checkup_time ? \Carbon\Carbon::parse($nextCheckup->checkup_time)->format('g:i A') : null;
            }

            return response()->json($checkupData);
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

    // Get patients with active prenatal records for AJAX search
    public function getPatientsWithActivePrenatalRecords(Request $request)
    {
        try {
            $patients = Patient::whereHas('prenatalRecords', function($query) {
                $query->where('is_active', true)
                      ->where('status', '!=', 'completed');
            })->get();

            return response()->json($patients);
        } catch (\Exception $e) {
            \Log::error('Error fetching patients with active prenatal records: ' . $e->getMessage());
            return response()->json([], 500);
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

    /**
     * Check for today's missed checkups and mark them automatically
     * Called when loading the index page if after business hours (5 PM)
     */
    private function checkTodaysMissed()
    {
        // If it's after 5 PM, mark today's upcoming checkups as missed
        if (now()->hour >= 17) {
            $missedCheckups = PrenatalCheckup::where('status', 'upcoming')
                ->whereDate('checkup_date', today())
                ->get();

            foreach ($missedCheckups as $checkup) {
                $checkup->update([
                    'status' => 'missed',
                    'missed_date' => now(),
                    'auto_missed' => true,
                    'missed_reason' => 'Did not show up for upcoming appointment'
                ]);
            }
        }
    }

    /**
     * Manually mark a checkup as missed
     */
    public function markAsMissed(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        try {
            $checkup = PrenatalCheckup::with('prenatalRecord.patient')->findOrFail($id);

            // Only allow marking upcoming checkups as missed
            if ($checkup->status !== 'upcoming') {
                return redirect()->back()
                    ->with('error', 'Only upcoming checkups can be marked as missed.');
            }

            $checkup->update([
                'status' => 'missed',
                'missed_date' => now(),
                'auto_missed' => false,
                'missed_reason' => $request->reason ?? 'Patient did not show up'
            ]);

            // Get patient name
            $patientName = $checkup->prenatalRecord->patient->first_name . ' ' .
                          $checkup->prenatalRecord->patient->last_name;

            // Send notification about missed checkup
            $this->notifyHealthcareWorkers(
                'Prenatal Checkup Missed',
                "Prenatal checkup for patient '{$patientName}' scheduled for " .
                $checkup->checkup_date->format('M j, Y') . " has been marked as missed.",
                'warning',
                Auth::user()->role === 'midwife'
                    ? route('midwife.prenatalcheckup.show', $checkup->id)
                    : route('bhw.prenatalcheckup.show', $checkup->id),
                [
                    'checkup_id' => $checkup->id,
                    'action' => 'checkup_missed',
                    'patient_name' => $patientName
                ]
            );

            return redirect()->back()
                ->with('success', 'Checkup marked as missed. Patient can now reschedule.');

        } catch (\Exception $e) {
            \Log::error('Error marking checkup as missed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error marking checkup as missed. Please try again.');
        }
    }

    /**
     * Reschedule a missed checkup
     * IMPORTANT: This creates a NEW upcoming checkup and keeps the missed record as history
     */
    public function rescheduleMissed(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'new_checkup_date' => 'required|date|after:today',
            'new_checkup_time' => 'required|date_format:H:i',
            'reschedule_notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $missedCheckup = PrenatalCheckup::with('prenatalRecord.patient')->findOrFail($id);

            // Only allow rescheduling missed checkups
            if ($missedCheckup->status !== 'missed') {
                return redirect()->back()
                    ->with('error', 'Only missed checkups can be rescheduled.');
            }

            // Check if a checkup already exists on the new date
            $existingCheckup = PrenatalCheckup::where('patient_id', $missedCheckup->patient_id)
                ->whereDate('checkup_date', $request->new_checkup_date)
                ->whereIn('status', ['upcoming', 'done'])
                ->first();

            if ($existingCheckup) {
                return redirect()->back()
                    ->withErrors(['new_checkup_date' => 'A checkup already exists for this patient on the selected date.'])
                    ->withInput();
            }

            // Create a NEW checkup record for the rescheduled appointment
            // This preserves the original missed checkup record as history
            $newCheckup = PrenatalCheckup::create([
                'patient_id' => $missedCheckup->patient_id,
                'prenatal_record_id' => $missedCheckup->prenatal_record_id,
                'checkup_date' => $request->new_checkup_date,
                'checkup_time' => $request->new_checkup_time,
                'status' => 'upcoming',
                'conducted_by' => Auth::id(),
                'notes' => "Rescheduled from missed appointment on " .
                          $missedCheckup->checkup_date->format('M j, Y') .
                          ($request->reschedule_notes ? ".\nReason: " . $request->reschedule_notes : ''),
            ]);

            // Update the missed checkup to indicate it has been rescheduled
            // Keep it as 'missed' status so it remains in the history
            $missedCheckup->update([
                'notes' => ($missedCheckup->notes ?? '') . "\n\n[RESCHEDULED] This appointment was rescheduled to " .
                          Carbon::parse($request->new_checkup_date)->format('M j, Y') . " at " .
                          Carbon::parse($request->new_checkup_time)->format('g:i A') .
                          " (New Checkup ID: " . $newCheckup->id . ")"
            ]);

            // Get patient name
            $patientName = $missedCheckup->prenatalRecord->patient->first_name . ' ' .
                          $missedCheckup->prenatalRecord->patient->last_name;

            // Send notification about rescheduling
            $formattedDate = Carbon::parse($request->new_checkup_date)->format('F j, Y');
            $formattedTime = Carbon::parse($request->new_checkup_time)->format('g:i A');

            $this->notifyHealthcareWorkers(
                'Prenatal Checkup Rescheduled',
                "Missed prenatal checkup for patient '{$patientName}' has been rescheduled to {$formattedDate} at {$formattedTime}.",
                'info',
                Auth::user()->role === 'midwife'
                    ? route('midwife.prenatalcheckup.show', $newCheckup->id)
                    : route('bhw.prenatalcheckup.show', $newCheckup->id),
                [
                    'checkup_id' => $newCheckup->id,
                    'original_missed_checkup_id' => $missedCheckup->id,
                    'action' => 'checkup_rescheduled',
                    'patient_name' => $patientName,
                    'new_date' => $formattedDate,
                    'new_time' => $formattedTime
                ]
            );

            // Send SMS reminder to patient
            $patient = $missedCheckup->prenatalRecord->patient;
            if ($patient->contact_number) {
                try {
                    $smsService = new SmsService();
                    $smsService->sendAppointmentReminder(
                        $patient->contact_number,
                        $patientName,
                        $formattedDate . ' at ' . $formattedTime,
                        'prenatal checkup'
                    );
                    \Log::info('SMS reminder sent for rescheduled prenatal checkup', [
                        'patient_id' => $patient->id,
                        'checkup_id' => $newCheckup->id,
                        'phone' => $patient->contact_number
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send SMS for rescheduled checkup: ' . $e->getMessage());
                    // Don't fail the reschedule if SMS fails
                }
            }

            return redirect()->back()
                ->with('success', "Checkup successfully rescheduled to {$formattedDate} at {$formattedTime}. The missed appointment record has been preserved for tracking." . ($patient->contact_number ? " SMS reminder sent to patient." : ""));

        } catch (\Exception $e) {
            \Log::error('Error rescheduling missed checkup: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error rescheduling checkup. Please try again.');
        }
    }
}