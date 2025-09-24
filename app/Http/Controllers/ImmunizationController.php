<?php

namespace App\Http\Controllers;

use App\Models\Immunization;
use App\Models\ChildRecord;
use App\Models\Vaccine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Notifications\HealthcareNotification;

class ImmunizationController extends Controller
{
    /**
     * Display a listing of immunization records
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        if (!in_array($user->role, ['midwife', 'bhw'])) {
            abort(403, 'Unauthorized access');
        }

        // Base query with relationships
        $query = Immunization::with(['childRecord', 'vaccine']);

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('childRecord', function ($childQuery) use ($request) {
                    $childQuery->where('first_name', 'like', "%{$request->search}%")
                               ->orWhere('middle_name', 'like', "%{$request->search}%")
                               ->orWhere('last_name', 'like', "%{$request->search}%");
                })->orWhereHas('vaccine', function ($vaccineQuery) use ($request) {
                    $vaccineQuery->where('name', 'like', "%{$request->search}%");
                })->orWhere('vaccine_name', 'like', "%{$request->search}%"); // Fallback for old records
            });
        }

        // Status filter - default to 'Upcoming' if no status is specified
        $status = $request->get('status', 'Upcoming');
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Vaccine filter
        if ($request->filled('vaccine')) {
            $query->where(function ($q) use ($request) {
                $q->where('vaccine_id', $request->vaccine)
                  ->orWhere('vaccine_name', $request->vaccine); // Fallback for old records
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('schedule_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('schedule_date', '<=', $request->date_to);
        }

        // Sorting
        $sortField = $request->get('sort', 'schedule_date');
        $sortDirection = $request->get('direction', 'asc');

        if (in_array($sortField, ['schedule_date', 'created_at', 'vaccine_name'])) {
            $query->orderBy($sortField, $sortDirection);
        } elseif ($sortField === 'child_name') {
            // Sort by child's first name, then last name
            $query->join('child_records', 'immunizations.child_record_id', '=', 'child_records.id')
                  ->orderBy('child_records.first_name', $sortDirection)
                  ->orderBy('child_records.last_name', $sortDirection)
                  ->select('immunizations.*'); // Select only immunizations columns to avoid conflicts
        } else {
            // Default sorting
            $query->orderBy('schedule_date', $sortDirection);
        }

        // Paginate results
        $immunizations = $query->paginate(10)->appends($request->query());

        // Get child records and available vaccines for dropdowns
        $childRecords = ChildRecord::orderBy('first_name')->orderBy('last_name')->get();
        $availableVaccines = Vaccine::orderBy('name')->get();

        // Statistics
        $stats = [
            'total' => Immunization::count(),
            'upcoming' => Immunization::where('status', 'Upcoming')->count(),
            'missed' => Immunization::where('status', 'Missed')->count(),
            'done' => Immunization::where('status', 'Done')->count()
        ];

        // View path based on role
        $viewPath = $user->role === 'bhw'
            ? 'bhw.immunization.index'
            : 'midwife.immunization.index';

        return view($viewPath, compact('immunizations', 'childRecords', 'availableVaccines', 'stats'))->with('currentStatus', $status);
    }

    /**
     * Store a newly created immunization record
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        if (!in_array($user->role, ['midwife', 'bhw'])) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'child_record_id' => 'required|exists:child_records,id',
            'vaccine_id' => 'required|exists:vaccines,id',
            'dose' => 'required|string|max:255',
            'schedule_date' => 'required|date|after_or_equal:today',
            'schedule_time' => 'required|date_format:H:i',
            'notes' => 'required|string|max:1000'
        ], [
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
        ]);

        try {
            DB::beginTransaction();

            // Check vaccine availability
            $vaccine = Vaccine::findOrFail($validated['vaccine_id']);
            if ($vaccine->current_stock <= 0) {
                return back()->withInput()
                           ->withErrors(['vaccine_id' => "The vaccine '{$vaccine->name}' is currently out of stock."]);
            }

            // Check if child already has an upcoming immunization
            $existingUpcoming = Immunization::where('child_record_id', $validated['child_record_id'])
                                          ->where('status', 'Upcoming')
                                          ->first();

            if ($existingUpcoming) {
                return back()->withInput()
                           ->withErrors(['child_record_id' => 'This child already has an upcoming immunization scheduled. Please complete or reschedule the existing one first.']);
            }

            // Prepare immunization data
            $immunizationData = $validated;
            $immunizationData['status'] = 'Upcoming';
            $immunizationData['vaccine_name'] = $vaccine->name; // Store for backward compatibility

            // Calculate next due date
            $immunizationData['next_due_date'] = $this->calculateNextDueDate(
                $vaccine->name,
                $validated['dose'],
                $validated['schedule_date']
            );

            $immunization = Immunization::create($immunizationData);

            // Send notification to all healthcare workers
            $child = ChildRecord::findOrFail($validated['child_record_id']);
            $this->notifyHealthcareWorkers(
                'New Immunization Scheduled',
                "Immunization for {$vaccine->name} has been scheduled for {$child->full_name} on " . Carbon::parse($validated['schedule_date'])->format('M d, Y'),
                'info',
                $user->role === 'midwife'
                    ? route('midwife.immunization.index')
                    : route('bhw.immunization.index'),
                ['immunization_id' => $immunization->id, 'child_id' => $child->id, 'action' => 'immunization_scheduled']
            );

            DB::commit();

            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';

            return redirect()->route($redirectRoute)
                             ->with('success', 'Immunization schedule created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating immunization record: ' . $e->getMessage());
            return back()->withInput()
                        ->withErrors(['error' => 'Error creating immunization schedule. Please try again.']);
        }
    }

    /**
     * Update an existing immunization record
     */
    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        if (!in_array($user->role, ['midwife', 'bhw'])) {
            abort(403, 'Unauthorized access');
        }

        try {
            $immunization = Immunization::with('vaccine')->findOrFail($id);
        } catch (\Exception $e) {
            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';
            return redirect()->route($redirectRoute)->with('error', 'Record not found.');
        }

        $validated = $request->validate([
            'child_record_id' => 'required|exists:child_records,id',
            'vaccine_id' => 'required|exists:vaccines,id',
            'dose' => 'required|string|max:255',
            'schedule_date' => 'required|date',
            'schedule_time' => 'required|date_format:H:i',
            'status' => ['required', Rule::in(['Upcoming', 'Done', 'Missed'])],
            'notes' => 'required|string|max:1000'
        ], [
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
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
            'notes.required' => 'Notes are required.',
            'notes.max' => 'Notes cannot exceed 1000 characters.'
        ]);

        try {
            DB::beginTransaction();

            // If changing to Done status, check and consume stock
            if ($validated['status'] === 'Done' && $immunization->status !== 'Done') {
                $vaccine = Vaccine::findOrFail($validated['vaccine_id']);
                
                if ($vaccine->current_stock <= 0) {
                    return back()->withInput()
                               ->withErrors(['status' => "Cannot mark as done - vaccine '{$vaccine->name}' is out of stock."]);
                }

                // Consume vaccine stock
                $vaccine->updateStock(1, 'out', "Immunization administered to {$immunization->childRecord->full_name}");
            }

            // If changing vaccine, check availability
            if ($validated['vaccine_id'] != $immunization->vaccine_id) {
                $newVaccine = Vaccine::findOrFail($validated['vaccine_id']);
                if ($newVaccine->current_stock <= 0) {
                    return back()->withInput()
                               ->withErrors(['vaccine_id' => "The vaccine '{$newVaccine->name}' is currently out of stock."]);
                }
                $validated['vaccine_name'] = $newVaccine->name;
            }

            // Calculate next due date
            $vaccine = Vaccine::findOrFail($validated['vaccine_id']);
            $validated['next_due_date'] = $this->calculateNextDueDate(
                $vaccine->name,
                $validated['dose'],
                $validated['schedule_date']
            );

            $oldStatus = $immunization->status;
            $immunization->update($validated);

            // Send notification if status changed to Done
            if ($oldStatus !== 'Done' && $validated['status'] === 'Done') {
                $child = ChildRecord::findOrFail($validated['child_record_id']);
                $this->notifyHealthcareWorkers(
                    'Immunization Completed',
                    "Immunization for {$vaccine->name} has been completed for {$child->full_name}",
                    'success',
                    $user->role === 'midwife'
                        ? route('midwife.immunization.index')
                        : route('bhw.immunization.index'),
                    ['immunization_id' => $immunization->id, 'child_id' => $child->id, 'action' => 'immunization_completed']
                );
            }

            DB::commit();

            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';

            return redirect()->route($redirectRoute)
                             ->with('success', 'Immunization record updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating immunization record: ' . $e->getMessage());
            return back()->withInput()
                        ->withErrors(['error' => 'Error updating record. Please try again.']);
        }
    }

    /**
     * Mark immunization as Done or Missed
     */
    public function markStatus($id, $status)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        if (!in_array($user->role, ['midwife', 'bhw'])) {
            abort(403, 'Unauthorized access');
        }

        try {
            DB::beginTransaction();

            $immunization = Immunization::with(['vaccine', 'childRecord'])->findOrFail($id);

            if (!in_array($status, ['Done', 'Missed'])) {
                return redirect()->back()->with('error', 'Invalid status update.');
            }

            // If marking as Done, consume vaccine stock
            if ($status === 'Done' && $immunization->status !== 'Done') {
                if (!$immunization->vaccine) {
                    return redirect()->back()
                                   ->with('error', 'Cannot mark as done - vaccine information is missing.');
                }

                if ($immunization->vaccine->current_stock <= 0) {
                    return redirect()->back()
                                   ->with('error', "Cannot mark as done - vaccine '{$immunization->vaccine->name}' is out of stock.");
                }

                // Consume vaccine stock
                $immunization->vaccine->updateStock(
                    1, 
                    'out', 
                    "Immunization administered to {$immunization->childRecord->full_name}"
                );
            }

            $immunization->status = $status;
            $immunization->save();

            DB::commit();

            return redirect()->back()->with('success', "Immunization marked as {$status}.");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating immunization status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating status. Please try again.');
        }
    }

    /**
     * Delete an immunization record
     */
    public function destroy(Immunization $immunization)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        if (!in_array($user->role, ['midwife', 'bhw'])) {
            abort(403, 'Unauthorized access');
        }

        try {
            $childName = $immunization->childRecord->full_name ?? 'Unknown';
            $vaccineName = $immunization->vaccine_name;

            $immunization->delete();

            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';

            return redirect()->route($redirectRoute)
                             ->with('success', "Immunization record for {$childName} ({$vaccineName}) has been deleted successfully!");
        } catch (\Exception $e) {
            \Log::error('Error deleting immunization record: ' . $e->getMessage());

            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';

            return redirect()->route($redirectRoute)
                             ->withErrors(['error' => 'Error deleting record. Please try again.']);
        }
    }

    /**
     * Get available vaccines for AJAX requests
     */
    public function getAvailableVaccines()
    {
        $vaccines = Vaccine::where('current_stock', '>', 0)
                          ->select('id', 'name', 'current_stock', 'category')
                          ->orderBy('name')
                          ->get()
                          ->map(function ($vaccine) {
                              return [
                                  'id' => $vaccine->id,
                                  'text' => "{$vaccine->name} (Stock: {$vaccine->current_stock})",
                                  'category' => $vaccine->category,
                                  'stock' => $vaccine->current_stock
                              ];
                          });

        return response()->json($vaccines);
    }

    /**
     * Calculate next due date based on vaccine type and dose
     */
    private function calculateNextDueDate($vaccineName, $dose, $currentDate)
    {
        $date = Carbon::parse($currentDate);

        $intervals = [
            'BCG' => null,
            'Hepatitis B' => [
                '1st Dose' => 30,
                '2nd Dose' => 150,
                '3rd Dose' => null
            ],
            'DPT' => [
                '1st Dose' => 30,
                '2nd Dose' => 30,
                '3rd Dose' => 365,
                'Booster' => null
            ],
            'OPV' => [
                '1st Dose' => 30,
                '2nd Dose' => 30,
                '3rd Dose' => null
            ],
            'MMR' => [
                '1st Dose' => 365,
                '2nd Dose' => null
            ]
        ];

        if (isset($intervals[$vaccineName])) {
            if (is_array($intervals[$vaccineName])) {
                $daysToAdd = $intervals[$vaccineName][$dose] ?? null;
            } else {
                $daysToAdd = $intervals[$vaccineName];
            }

            if ($daysToAdd) {
                return $date->addDays($daysToAdd)->toDateString();
            }
        }

        return null;
    }

    /**
     * Get available vaccines for a specific child (AJAX endpoint)
     */
    public function getAvailableVaccinesForChild($childId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        if (!in_array($user->role, ['midwife', 'bhw'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $vaccines = Immunization::getAvailableVaccinesForChild($childId);

            return response()->json([
                'success' => true,
                'vaccines' => $vaccines->map(function ($vaccine) {
                    return [
                        'id' => $vaccine->id,
                        'name' => $vaccine->name,
                        'category' => $vaccine->category
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load available vaccines'
            ], 500);
        }
    }

    /**
     * Get available doses for a specific vaccine and child (AJAX endpoint)
     */
    public function getAvailableDosesForChild($childId, $vaccineId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        if (!in_array($user->role, ['midwife', 'bhw'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $vaccine = Vaccine::findOrFail($vaccineId);
            $availableDoses = Immunization::getAvailableDosesForChild($childId, $vaccine->name);

            \Log::info('Available doses for child', [
                'child_id' => $childId,
                'vaccine_id' => $vaccineId,
                'vaccine_name' => $vaccine->name,
                'available_doses' => $availableDoses
            ]);

            // Create dose options array
            $doseOptions = [];
            if (!empty($availableDoses)) {
                $doseOptions = array_combine($availableDoses, $availableDoses);
            }

            return response()->json([
                'success' => true,
                'doses' => $doseOptions
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading available doses', [
                'child_id' => $childId,
                'vaccine_id' => $vaccineId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load available doses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick update immunization status via AJAX
     */
    public function quickUpdateStatus(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $user = Auth::user();

        if (!in_array($user->role, ['midwife', 'bhw'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:Done,Missed,Upcoming',
            'administered_by' => 'nullable|string|max:255',
            'batch_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $immunization = Immunization::with(['vaccine', 'childRecord'])->findOrFail($id);

            // If marking as Done, consume vaccine stock
            if ($validated['status'] === 'Done' && $immunization->status !== 'Done') {
                if (!$immunization->vaccine) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot mark as done - vaccine information is missing.'
                    ], 400);
                }

                if ($immunization->vaccine->current_stock <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot mark as done - vaccine '{$immunization->vaccine->name}' is out of stock."
                    ], 400);
                }

                // Consume vaccine stock
                $immunization->vaccine->updateStock(
                    1,
                    'out',
                    "Immunization administered to {$immunization->childRecord->full_name}"
                );

                // Create child immunization record
                \App\Models\ChildImmunization::create([
                    'child_record_id' => $immunization->child_record_id,
                    'vaccine_name' => $immunization->vaccine->name ?? $immunization->vaccine_name,
                    'vaccine_description' => $immunization->vaccine->description ?? '',
                    'vaccination_date' => $immunization->schedule_date,
                    'administered_by' => $validated['administered_by'] ?? $user->name,
                    'batch_number' => $validated['batch_number'],
                    'notes' => $validated['notes'] ?? 'Marked done via quick action'
                ]);
            }

            // Update immunization status
            $immunization->status = $validated['status'];
            $immunization->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Immunization marked as {$validated['status']} successfully!",
                'data' => [
                    'id' => $immunization->id,
                    'status' => $immunization->status
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating immunization status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error updating status. Please try again.'
            ], 500);
        }
    }

    /**
     * Send notification to all healthcare workers
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