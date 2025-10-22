<?php

namespace App\Http\Controllers;
use App\Models\ChildRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient;
use App\Notifications\HealthcareNotification;
use Illuminate\Support\Facades\Cache;
use App\Services\ChildRecordService;
use App\Http\Requests\StoreChildRecordRequest;
use App\Http\Requests\UpdateChildRecordRequest;

class ChildRecordController extends Controller
{
    protected $childRecordService;

    public function __construct(ChildRecordService $childRecordService)
    {
        $this->childRecordService = $childRecordService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!Auth::check()) abort(401, 'Authentication required');

        $user = Auth::user();
        if (!in_array($user->role, ['midwife', 'bhw'])) abort(403, 'Unauthorized access');

        $query = ChildRecord::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('middle_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('phone_number', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('gender')) $query->where('gender', $request->gender);

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        if (in_array($sortField, ['child_name', 'birthdate', 'created_at'])) {
            if ($sortField === 'child_name') {
                $query->orderBy('first_name', $sortDirection)->orderBy('last_name', $sortDirection);
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        }

        $childRecords = $query->paginate(10)->appends($request->query());

        // Get mothers who completed pregnancy for the add modal
        $mothers = Patient::whereHas('prenatalRecords', function ($q) {
            $q->where('status', 'completed');
        })->get();

        $viewPath = $user->role === 'bhw' ? 'bhw.childrecord.index' : 'midwife.childrecord.index';

        return view($viewPath, compact('childRecords', 'mothers'));
    }

    /**
     * AJAX search for child records (real-time search)
     */
    public function search(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $user = Auth::user();
        if (!in_array($user->role, ['midwife', 'bhw'])) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $query = ChildRecord::query();

        // Apply search filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('middle_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('phone_number', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Apply sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        if (in_array($sortField, ['first_name', 'last_name', 'birthdate', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $childRecords = $query->paginate(10)->appends($request->query());

        // Return HTML for the table content
        $viewPath = $user->role === 'bhw' ? 'bhw.childrecord.table' : 'midwife.childrecord.table';

        $html = view($viewPath, compact('childRecords'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'pagination' => $childRecords->links()->render()
        ]);
    }

    /**
     * Show the form for creating a new child record
     */
    public function create()
    {
        if (!Auth::check()) abort(401, 'Authentication required');

        $user = Auth::user();
        if (!in_array($user->role, ['midwife', 'bhw'])) abort(403, 'Unauthorized access');

        // Get mothers who completed pregnancy
        $mothers = Patient::whereHas('prenatalRecords', function ($q) {
            $q->where('status', 'completed');
        })->get();

        $viewPath = $user->role === 'bhw' ? 'bhw.childrecord.create' : 'midwife.childrecord.create';

        return view($viewPath, compact('mothers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChildRecordRequest $request)
    {
        $user = Auth::user();

        try {
            $childRecord = $this->childRecordService->createChildRecord($request->validated());

            $redirectRoute = $user->role === 'bhw' ? 'bhw.childrecord.index' : 'midwife.childrecord.index';

            return redirect()->route($redirectRoute)
                             ->with('success', 'Child record created successfully!');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ChildRecord $childrecord)
    {
        // Check authorization
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.'
                ], 401);
            }
            abort(401, 'Authentication required');
        }

        $user = Auth::user();
        
        // Authorize roles
        if (!in_array($user->role, ['midwife', 'bhw'])) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            abort(403, 'Unauthorized access');
        }

        // Load immunizations and mother relationships
        $childrecord->load([
            'immunizations' => function($query) {
                $query->orderBy('schedule_date', 'desc');
            },
            'mother'
        ]);

        // If it's an AJAX request, return JSON
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $childrecord
            ]);
        }

        // For regular requests, return the view
        $viewPath = $user->role === 'bhw' ? 'bhw.childrecord.show' : 'midwife.childrecord.show';

        return view($viewPath, [
            'childRecord' => $childrecord  // Keep original variable name for views
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChildRecord $childrecord)
    {
        // Check authorization
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.'
                ], 401);
            }
            abort(401, 'Authentication required');
        }

        $user = Auth::user();
        
        // Authorize roles
        if (!in_array($user->role, ['midwife', 'bhw'])) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            abort(403, 'Unauthorized access');
        }

        // Return JSON for AJAX requests or view for regular requests
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $childrecord
            ]);
        }

        // Role-based redirect with edit flag
        $redirectRoute = $user->role === 'bhw'
            ? 'bhw.childrecord.index'
            : 'midwife.childrecord.index';

        return redirect()->route($redirectRoute)->with('edit_record', $childrecord);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChildRecordRequest $request, $id)
    {
        $user = Auth::user();

        try {
            $childrecord = ChildRecord::findOrFail($id);
            $childrecord = $this->childRecordService->updateChildRecord($childrecord, $request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Child record updated successfully!',
                    'data' => $childrecord->fresh()
                ], 200);
            }

            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.childrecord.index'
                : 'midwife.childrecord.index';

            return redirect()->route($redirectRoute)
                            ->with('success', 'Child record updated successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found.'
                ], 404);
            }

            $redirectRoute = $user->role === 'bhw'
                ? 'bhw.childrecord.index'
                : 'midwife.childrecord.index';

            return redirect()->route($redirectRoute)
                            ->with('error', 'Record not found.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating record. Please try again.'
                ], 500);
            }

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChildRecord $childRecord)
    {
        // Check authorization
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.'
                ], 401);
            }
            abort(401, 'Authentication required');
        }

        $user = Auth::user();
        
        // Authorize roles
        if (!in_array($user->role, ['midwife', 'bhw'])) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            abort(403, 'Unauthorized access');
        }

        try {
            $childName = $childRecord->full_name;
            $childRecord->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Child record for {$childName} has been deleted successfully!"
                ]);
            }

            // Role-based redirect
            $redirectRoute = $user->role === 'bhw' 
                ? 'bhw.childrecord.index' 
                : 'midwife.childrecord.index';

            return redirect()->route($redirectRoute)
                            ->with('success', "Child record for {$childName} has been deleted successfully!");
        } catch (\Exception $e) {
            \Log::error('Error deleting child record: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting record. Please try again.'
                ], 500);
            }

            // Role-based redirect for errors
            $redirectRoute = $user->role === 'bhw' 
                ? 'bhw.childrecord.index' 
                : 'midwife.childrecord.index';

            return redirect()->route($redirectRoute)
                           ->withErrors(['error' => 'Error deleting record. Please try again.']);
        }
    }

}