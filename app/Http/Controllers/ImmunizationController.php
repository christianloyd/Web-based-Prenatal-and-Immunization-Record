<?php

namespace App\Http\Controllers;

use App\Models\Immunization;
use App\Models\ChildRecord;
use App\Models\Vaccine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
                    $childQuery->where('child_name', 'like', "%{$request->search}%");
                })->orWhereHas('vaccine', function ($vaccineQuery) use ($request) {
                    $vaccineQuery->where('name', 'like', "%{$request->search}%");
                })->orWhere('vaccine_name', 'like', "%{$request->search}%"); // Fallback for old records
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        if (in_array($sortField, ['schedule_date', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            // Default sorting
            $query->orderBy('schedule_date', $sortDirection);
        }

        // Paginate results
        $immunizations = $query->paginate(10)->appends($request->query());

        // Get child records and available vaccines for dropdowns
        $childRecords = ChildRecord::orderBy('child_name')->get();
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

        return view($viewPath, compact('immunizations', 'childRecords', 'availableVaccines', 'stats'));
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
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Check vaccine availability
            $vaccine = Vaccine::findOrFail($validated['vaccine_id']);
            if ($vaccine->current_stock <= 0) {
                return back()->withInput()
                           ->withErrors(['vaccine_id' => "The vaccine '{$vaccine->name}' is currently out of stock."]);
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

            Immunization::create($immunizationData);

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
            'notes' => 'nullable|string|max:1000'
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
                $vaccine->updateStock(1, 'out', "Immunization administered to {$immunization->childRecord->child_name}");
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

            $immunization->update($validated);

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
                    "Immunization administered to {$immunization->childRecord->child_name}"
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
            $childName = $immunization->childRecord->child_name ?? 'Unknown';
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
}