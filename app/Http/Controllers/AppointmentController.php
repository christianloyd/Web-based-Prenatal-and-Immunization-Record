<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\PrenatalRecord;
use App\Models\Patient;
use App\Models\User;
use App\Models\PrenatalCheckup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Notifications\HealthcareNotification;
use Illuminate\Support\Facades\Cache;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        $query = Appointment::with(['patient', 'prenatalRecord', 'conductedBy', 'prenatalCheckup'])
                           ->orderBy('appointment_date', 'desc')
                           ->orderBy('appointment_time', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->whereHas('patient', function ($patientQuery) use ($term) {
                    $patientQuery->where('first_name', 'LIKE', "%{$term}%")
                              ->orWhere('last_name', 'LIKE', "%{$term}%")
                              ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%");
                })
                ->orWhere('formatted_appointment_id', 'LIKE', "%{$term}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }

        $appointments = $query->paginate(20)->withQueryString();

        // Get patients with active prenatal records
        $patients = Patient::with(['prenatalRecords' => function($query) {
            $query->where('is_active', true);
        }])->whereHas('prenatalRecords', function($query) {
            $query->where('is_active', true);
        })->get();

        // Get prenatal records for the modal dropdown
        $prenatalRecords = PrenatalRecord::with('patient')->where('is_active', true)->get();

        // Get users for conducted_by dropdown
        $healthcareWorkers = User::whereIn('role', ['midwife', 'bhw'])->orderBy('name')->get();

        // Return appropriate view based on user role
        $view = auth()->user()->role === 'midwife'
            ? 'midwife.appointments.index'
            : 'bhw.appointments.index';

        return view($view, compact('appointments', 'patients', 'prenatalRecords', 'healthcareWorkers'));
    }

    public function create()
    {
        // Get active prenatal records
        $prenatalRecords = PrenatalRecord::with('patient')->where('is_active', true)->get();

        // Get patients without active appointments for quick scheduling
        $patients = Patient::with(['prenatalRecords' => function($query) {
            $query->where('is_active', true);
        }])->whereHas('prenatalRecords', function($query) {
            $query->where('is_active', true);
        })->get();

        // Get healthcare workers
        $healthcareWorkers = User::whereIn('role', ['midwife', 'bhw'])->orderBy('name')->get();

        $view = auth()->user()->role === 'midwife'
            ? 'midwife.appointments.create'
            : 'bhw.appointments.create';

        return view($view, compact('prenatalRecords', 'patients', 'healthcareWorkers'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'prenatal_record_id' => 'nullable|exists:prenatal_records,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'type' => 'required|in:prenatal_checkup,follow_up,consultation,emergency',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $appointment = Appointment::create([
                'patient_id' => $request->patient_id,
                'prenatal_record_id' => $request->prenatal_record_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'type' => $request->type,
                'status' => 'scheduled',
                'notes' => $request->notes,
            ]);

            // Get patient for notification
            $patient = Patient::find($request->patient_id);

            // Send notification to all healthcare workers about new appointment
            $this->notifyHealthcareWorkers(
                'New Appointment Scheduled',
                "A new {$appointment->type_text} appointment has been scheduled for patient '{$patient->first_name} {$patient->last_name}' on " . $appointment->formatted_appointment_date_time,
                'info',
                Auth::user()->role === 'midwife'
                    ? route('midwife.appointments.show', $appointment->id)
                    : route('bhw.appointments.show', $appointment->id),
                ['appointment_id' => $appointment->id, 'patient_id' => $patient->id, 'action' => 'appointment_created']
            );

            $redirectRoute = Auth::user()->role === 'midwife'
                ? 'midwife.appointments.index'
                : 'bhw.appointments.index';

            return redirect()->route($redirectRoute)
                ->with('success', 'Appointment scheduled successfully!');

        } catch (\Exception $e) {
            \Log::error('Error creating appointment: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error scheduling appointment.')
                ->withInput();
        }
    }

    public function show($id)
    {
        $appointment = Appointment::with([
            'patient',
            'prenatalRecord',
            'conductedBy',
            'prenatalCheckup'
        ])->findOrFail($id);

        $view = auth()->user()->role === 'midwife'
            ? 'midwife.appointments.show'
            : 'bhw.appointments.show';

        return view($view, compact('appointment'));
    }

    public function markCompleted($id)
    {
        try {
            $appointment = Appointment::with(['patient'])->findOrFail($id);

            if (!$appointment->canBeCompleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This appointment cannot be marked as completed.'
                ], 400);
            }

            $appointment->markAsCompleted();

            // If this is a prenatal checkup appointment, create a prenatal checkup record
            if ($appointment->type === 'prenatal_checkup') {
                PrenatalCheckup::create([
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'prenatal_record_id' => $appointment->prenatal_record_id,
                    'status' => 'pending', // Medical data still needs to be filled
                ]);
            }

            $this->notifyHealthcareWorkers(
                'Appointment Completed',
                "Appointment for patient '{$appointment->patient->first_name} {$appointment->patient->last_name}' has been marked as completed.",
                'success',
                Auth::user()->role === 'midwife'
                    ? route('midwife.appointments.show', $appointment->id)
                    : route('bhw.appointments.show', $appointment->id),
                ['appointment_id' => $appointment->id, 'action' => 'appointment_completed']
            );

            return response()->json([
                'success' => true,
                'message' => 'Appointment marked as completed successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error marking appointment as completed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating appointment status.'
            ], 500);
        }
    }

    /**
     * Helper method to notify all healthcare workers about appointment events
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
