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
use App\Services\ImmunizationService;
use App\Http\Requests\StoreImmunizationRequest;
use App\Http\Requests\UpdateImmunizationRequest;

class ImmunizationController extends Controller
{
    protected $immunizationService;

    public function __construct(ImmunizationService $immunizationService)
    {
        $this->immunizationService = $immunizationService;
    }
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

        // Base query with relationships (including rescheduled relationship)
        $query = Immunization::with(['childRecord', 'vaccine', 'rescheduledToImmunization']);

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

        // OPTIMIZED: Build vaccine completion data with a single query
        // Get all completed doses grouped by child and vaccine
        // NOTE: selectRaw is safe here - no user input, static aggregation for performance
        $completedDosesData = Immunization::selectRaw('child_record_id, vaccine_id, COUNT(*) as completed_count')
            ->where('status', 'Done')
            ->groupBy('child_record_id', 'vaccine_id')
            ->get()
            ->groupBy('child_record_id')
            ->map(function ($childImmunizations) {
                return $childImmunizations->keyBy('vaccine_id');
            });

        // Build completion data structure efficiently
        $vaccineCompletionData = [];
        foreach ($childRecords as $child) {
            $vaccineCompletionData[$child->id] = [];
            $childDoses = $completedDosesData->get($child->id, collect());

            foreach ($availableVaccines as $vaccine) {
                $completedCount = $childDoses->get($vaccine->id)->completed_count ?? 0;
                $doseCount = $vaccine->dose_count ?? 0;
                $isCompleted = $doseCount > 0 && $completedCount >= $doseCount;
                $remaining = $doseCount > 0 ? max(0, $doseCount - $completedCount) : 0;

                // Calculate next dose label
                $nextDose = null;
                if (!$isCompleted && $doseCount > 0) {
                    $doseNumber = $completedCount + 1;
                    $nextDose = match($doseNumber) {
                        1 => '1st Dose',
                        2 => '2nd Dose',
                        3 => '3rd Dose',
                        default => $doseNumber . 'th Dose'
                    };
                }

                $vaccineCompletionData[$child->id][$vaccine->id] = [
                    'completed' => $isCompleted,
                    'remaining' => $remaining,
                    'next_dose' => $nextDose
                ];
            }
        }

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

        return view($viewPath, compact('immunizations', 'childRecords', 'availableVaccines', 'stats', 'vaccineCompletionData'))->with('currentStatus', $status);
    }

    /**
     * Store a newly created immunization record
     */
    public function store(StoreImmunizationRequest $request)
    {
        $user = Auth::user();

        try {
            $immunization = $this->immunizationService->createImmunization($request->validated());

            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Immunization schedule created successfully!',
                    'immunization' => $immunization
                ]);
            }

            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';

            return redirect()->route($redirectRoute)
                             ->with('success', 'Immunization schedule created successfully!');

        } catch (\Exception $e) {
            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()->withInput()
                        ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified immunization record
     */
    public function show($id)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        if (!in_array($user->role, ['midwife', 'bhw'])) {
            abort(403, 'Unauthorized access');
        }

        try {
            $immunization = Immunization::with(['childRecord', 'vaccine'])->findOrFail($id);

            // View path based on role
            $viewPath = $user->role === 'bhw'
                ? 'bhw.immunization.show'
                : 'midwife.immunization.show';

            return view($viewPath, compact('immunization'));

        } catch (\Exception $e) {
            \Log::error('Error loading immunization record: ' . $e->getMessage());

            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';

            return redirect()->route($redirectRoute)
                             ->with('error', 'Record not found.');
        }
    }

    /**
     * Update an existing immunization record
     */
    public function update(UpdateImmunizationRequest $request, $id)
    {
        $user = Auth::user();

        try {
            $immunization = Immunization::with('vaccine')->findOrFail($id);
            $immunization = $this->immunizationService->updateImmunization($immunization, $request->validated());

            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';

            return redirect()->route($redirectRoute)
                             ->with('success', 'Immunization record updated successfully!');

        } catch (\Exception $e) {
            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return redirect()->route($redirectRoute)->with('error', 'Record not found.');
            }

            return back()->withInput()
                        ->withErrors(['error' => $e->getMessage()]);
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
            $immunization = Immunization::with(['vaccine', 'childRecord'])->findOrFail($id);
            $this->immunizationService->markStatus($immunization, $status);

            return redirect()->back()->with('success', "Immunization marked as {$status}.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark immunization as missed
     */
    public function markAsMissed($id)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        if (!in_array($user->role, ['midwife', 'bhw'])) {
            abort(403, 'Unauthorized access');
        }

        try {
            $immunization = Immunization::with(['vaccine', 'childRecord'])->findOrFail($id);
            $this->immunizationService->markStatus($immunization, 'Missed');

            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';

            return redirect()->route($redirectRoute)
                             ->with('success', 'Immunization marked as missed.');

        } catch (\Exception $e) {
            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';

            return redirect()->route($redirectRoute)
                             ->with('error', $e->getMessage());
        }
    }

    /**
     * Reschedule a missed immunization
     */
    public function reschedule(Request $request, $id)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        if (!in_array($user->role, ['midwife', 'bhw'])) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'schedule_date' => 'required|date|after_or_equal:today',
            'schedule_time' => 'nullable|date_format:H:i'
        ]);

        try {
            $missedImmunization = Immunization::with(['vaccine', 'childRecord.mother'])->findOrFail($id);

            // Prepare schedule date and time
            $newScheduleDate = $validated['schedule_date'];
            $newScheduleTime = $validated['schedule_time'] ?? null;

            $newImmunization = Immunization::create([
                'child_record_id' => $missedImmunization->child_record_id,
                'vaccine_id' => $missedImmunization->vaccine_id,
                'vaccine_name' => $missedImmunization->vaccine_name,
                'dose' => $missedImmunization->dose,
                'schedule_date' => $newScheduleDate,
                'schedule_time' => $newScheduleTime,
                'status' => 'Upcoming',
                'notes' => 'Rescheduled from missed appointment on ' . \Carbon\Carbon::parse($missedImmunization->schedule_date)->format('M d, Y')
            ]);

            // Mark the original immunization as rescheduled and link to new appointment
            $missedImmunization->rescheduled = true;
            $missedImmunization->rescheduled_to_immunization_id = $newImmunization->id;
            $missedImmunization->save();

            

            // Send SMS if contact available
            $child = $missedImmunization->childRecord;
            $mother = $child ? $child->mother : null;
            $contactNumber = $mother ? $mother->contact : null;

            if ($contactNumber) {
                try {
                    $smsService = new \App\Services\SmsService();
                    $formattedDate = \Carbon\Carbon::parse($newScheduleDate)->format('F j, Y');
                    $formattedTime = $newScheduleTime ? \Carbon\Carbon::parse($newScheduleTime)->format('g:i A') : '';
                    $smsService->sendVaccinationReminder(
                        $contactNumber,
                        $child->full_name,
                        $missedImmunization->vaccine->name ?? $missedImmunization->vaccine_name,
                        $formattedDate . ($formattedTime ? ' at ' . $formattedTime : ''),
                        $mother->name ?? null
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send SMS for rescheduled immunization: ' . $e->getMessage());
                }
            }

            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';

            return redirect()->route($redirectRoute)
                             ->with('success', 'Immunization rescheduled successfully!');

        } catch (\Exception $e) {
            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';

            return redirect()->route($redirectRoute)
                             ->with('error', $e->getMessage());
        }
    }

    /**
     * Mark immunization as complete (Done)
     * Simple confirmation - changes status from Upcoming to Done
     */
    public function completeImmunization($id)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        // Only midwife and BHW can mark immunizations as complete
        if (!in_array($user->role, ['midwife', 'bhw'])) {
            abort(403, 'Unauthorized. Only midwives and BHWs can mark immunizations as complete.');
        }

        try {
            $immunization = Immunization::findOrFail($id);

            // Only allow completing upcoming immunizations
            if ($immunization->status !== 'Upcoming') {
                throw new \Exception('Only upcoming immunizations can be marked as complete.');
            }

            // Update status to Done
            $immunization->status = 'Done';
            $immunization->save();

            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';

            return redirect()->route($redirectRoute)
                ->with('success', 'Immunization marked as complete successfully!');

        } catch (\Exception $e) {
            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.immunization.index'
                : 'midwife.immunization.index';

            return redirect()->route($redirectRoute)
                ->with('error', $e->getMessage());
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
    public function quickUpdateStatus(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $user = Auth::user();

        if (!in_array($user->role, ['midwife', 'bhw'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'immunization_id' => 'required|integer|exists:immunizations,id',
            'status' => 'required|in:Done,Missed,Upcoming',
            'administered_by' => 'nullable|string|max:255',
            'batch_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'reason' => 'nullable|string|max:255',
            'reschedule' => 'nullable|boolean',
            'reschedule_date' => 'nullable|date',
            'reschedule_time' => 'nullable|date_format:H:i'
        ]);

        try {
            $immunization = Immunization::with(['vaccine', 'childRecord'])->findOrFail($validated['immunization_id']);
            $immunization = $this->immunizationService->quickUpdateStatus($immunization, $validated);

            return response()->json([
                'success' => true,
                'message' => "Immunization marked as {$validated['status']} successfully!",
                'data' => [
                    'id' => $immunization->id,
                    'status' => $immunization->status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Get all children for immunization search (simplified approach)
     */
    public function getChildrenForImmunization(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        if (!in_array($user->role, ['midwife', 'bhw'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $children = ChildRecord::with('mother')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
                ->map(function($child) {
                    $motherName = $child->mother ? $child->mother->name : ($child->mother_name ?? 'Unknown');

                    return [
                        'id' => $child->id,
                        'name' => $child->full_name,
                        'formatted_child_id' => $child->formatted_child_id,
                        'mother_name' => $motherName,
                        'age' => $child->age ?? 'Unknown age',
                        'gender' => $child->gender,
                        'search_text' => strtolower($child->full_name . ' ' . $child->formatted_child_id . ' ' . $motherName)
                    ];
                });

            return response()->json($children);

        } catch (\Exception $e) {
            \Log::error('Error loading children for immunization: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load children'], 500);
        }
    }

}