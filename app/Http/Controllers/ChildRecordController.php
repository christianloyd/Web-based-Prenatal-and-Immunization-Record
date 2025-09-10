<?php

namespace App\Http\Controllers;
use App\Models\ChildRecord;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient;

class ChildRecordController extends Controller
{
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
                $q->where('child_name', 'like', "%{$request->search}%")
                  ->orWhere('phone_number', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('gender')) $query->where('gender', $request->gender);

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        if (in_array($sortField, ['child_name', 'birthdate', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $childRecords = $query->paginate(10)->appends($request->query());

        $viewPath = $user->role === 'bhw' ? 'bhw.childrecord.index' : 'midwife.childrecord.index';

        return view($viewPath, compact('childRecords'));
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
    public function store(Request $request)
{
    if (!Auth::check()) abort(401, 'Authentication required');

    $user = Auth::user();
    if (!in_array($user->role, ['midwife', 'bhw'])) abort(403, 'Unauthorized access');

    // Define base validation rules
    $baseRules = [
        'child_name' => 'required|string|max:255|min:2',
        'gender' => ['required', Rule::in(['Male', 'Female'])],
        'birthdate' => 'required|date|before_or_equal:today|after:1900-01-01',
        'birth_height' => 'nullable|numeric|min:0|max:999.99',
        'birth_weight' => 'nullable|numeric|min:0|max:99.999',
        'birthplace' => 'nullable|string|max:255',
        'father_name' => 'nullable|string|max:255|min:2',
        'phone_number' => [
            'required',
            'string',
            'regex:/^(\+63|63|0)?[9]\d{9}$/',
            'min:10',
            'max:15'
        ],
        'address' => 'nullable|string|max:1000',
        'mother_exists' => 'required|in:yes,no'
    ];

    // Add conditional validation based on mother_exists
    $conditionalRules = [];
    
    if ($request->input('mother_exists') === 'yes') {
        $conditionalRules['mother_id'] = 'required|exists:patients,id';
    } else {
        $conditionalRules = array_merge($conditionalRules, [
            'mother_name' => 'required|string|max:255|min:2',
            'mother_age' => 'required|integer|min:15|max:50',
            'mother_contact' => [
                'required',
                'string',
                'regex:/^(\+63|63|0)?[9]\d{9}$/',
                'min:10',
                'max:15'
            ],
            'mother_address' => 'required|string|max:1000'
        ]);
    }

    $validationRules = array_merge($baseRules, $conditionalRules);

    // Custom error messages
    $messages = [
        'child_name.required' => 'Child name is required.',
        'child_name.min' => 'Child name must be at least 2 characters.',
        'mother_name.required' => 'Mother\'s name is required when adding a new mother.',
        'mother_name.min' => 'Mother\'s name must be at least 2 characters.',
        'mother_age.required' => 'Mother\'s age is required when adding a new mother.',
        'mother_age.min' => 'Mother must be at least 15 years old.',
        'mother_age.max' => 'Mother cannot be older than 50 years.',
        'mother_contact.required' => 'Mother\'s contact number is required when adding a new mother.',
        'mother_contact.regex' => 'Please enter a valid Philippine mobile number.',
        'mother_address.required' => 'Mother\'s address is required when adding a new mother.',
        'mother_id.required' => 'Please select a mother from the list.',
        'mother_id.exists' => 'Selected mother does not exist.',
        'phone_number.required' => 'Phone number is required.',
        'phone_number.regex' => 'Please enter a valid Philippine mobile number.',
        'birthdate.required' => 'Birth date is required.',
        'birthdate.before_or_equal' => 'Birth date cannot be in the future.',
        'gender.required' => 'Gender selection is required.',
        'mother_exists.required' => 'Please specify if mother exists in the system.',
    ];

    try {
        $validated = $request->validate($validationRules, $messages);
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation failed for child record creation', [
            'errors' => $e->errors(),
            'request_data' => $request->except(['_token'])
        ]);
        throw $e;
    }

    try {
        $motherId = null;

        // Handle mother creation/selection
        if ($validated['mother_exists'] === 'no') {
            // Create new mother (patient)
            try {
                $mother = Patient::create([
                    'name' => $validated['mother_name'],
                    'age' => $validated['mother_age'],
                    'contact' => $validated['mother_contact'],
                    'address' => $validated['mother_address'],
                    'formatted_patient_id' => Patient::generatePatientId()
                ]);
                $motherId = $mother->id;
                
                \Log::info('New mother created for child record', [
                    'mother_id' => $mother->id,
                    'mother_name' => $mother->name,
                    'created_by' => $user->id
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Error creating mother record: ' . $e->getMessage(), [
                    'mother_data' => [
                        'name' => $validated['mother_name'],
                        'age' => $validated['mother_age'],
                        'contact' => $validated['mother_contact'],
                        'address' => $validated['mother_address']
                    ],
                    'user_id' => $user->id
                ]);
                
                return back()->withInput()->withErrors([
                    'error' => 'Error creating mother record. Please try again.'
                ]);
            }
        } else {
            // Use existing mother
            $motherId = $validated['mother_id'];
            
            \Log::info('Using existing mother for child record', [
                'mother_id' => $motherId,
                'selected_by' => $user->id
            ]);
        }

        // Prepare child record data
        $childData = [
            'child_name' => $validated['child_name'],
            'gender' => $validated['gender'],
            'birthdate' => $validated['birthdate'],
            'birth_height' => $validated['birth_height'],
            'birth_weight' => $validated['birth_weight'],
            'birthplace' => $validated['birthplace'],
            'father_name' => $validated['father_name'],
            'phone_number' => $validated['phone_number'],
            'address' => $validated['address'],
            'mother_id' => $motherId
        ];

        // Create child record
        try {
            $childRecord = ChildRecord::create($childData);
            
            \Log::info('Child record created successfully', [
                'child_record_id' => $childRecord->id,
                'child_name' => $childRecord->child_name,
                'mother_id' => $motherId,
                'mother_exists' => $validated['mother_exists'],
                'created_by' => $user->id
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error creating child record: ' . $e->getMessage(), [
                'child_data' => $childData,
                'user_id' => $user->id,
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->withErrors([
                'error' => 'Error creating child record. Please try again.'
            ]);
        }

        $redirectRoute = $user->role === 'bhw' ? 'bhw.childrecord.index' : 'midwife.childrecord.index';
        
        return redirect()->route($redirectRoute)
                         ->with('success', 'Child record created successfully!');

    } catch (\Exception $e) {
        \Log::error('Unexpected error in child record store method: ' . $e->getMessage(), [
            'stack_trace' => $e->getTraceAsString(),
            'request_data' => $request->except(['_token']),
            'user_id' => $user->id
        ]);
        
        return back()->withInput()->withErrors([
            'error' => 'An unexpected error occurred. Please try again.'
        ]);
    }
}

    /**
     * Display the specified resource.
     */
    public function show(ChildRecord $childRecord)
    {
        // Check authorization
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.'
            ], 401);
        }

        $user = Auth::user();
        
        // Authorize roles
        if (!in_array($user->role, ['midwife', 'bhw'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        try {
            return response()->json([
                'success' => true,
                'data' => $childRecord
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.'
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
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
            $childRecord = ChildRecord::findOrFail($id);
            
            // Return JSON for AJAX requests or view for regular requests
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $childRecord
                ]);
            }
            
            // Role-based redirect with edit flag
            $redirectRoute = $user->role === 'bhw' 
                ? 'bhw.childrecord.index' 
                : 'midwife.childrecord.index';
            
            return redirect()->route($redirectRoute)->with('edit_record', $childRecord);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching child record for edit: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
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
        }
    }

 /**
 * Update the specified resource in storage.
 * IMPORTANT: Parameter name must match the route parameter
 */
public function update(Request $request, $id)
{
    // Check authorization
    if (!Auth::check()) {
        if ($request->expectsJson()) {
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
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }
        abort(403, 'Unauthorized access');
    }

    // Find the child record
    try {
        $childrecord = ChildRecord::findOrFail($id);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        \Log::error('Child record not found for update', [
            'id' => $id,
            'user' => $user->id
        ]);
        
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
    }

    // Validate the request
    $validated = $request->validate([
        'child_name' => 'required|string|max:255|min:2',
        'gender' => ['required', Rule::in(['Male', 'Female'])],
        'birthdate' => 'required|date|before_or_equal:today|after:1900-01-01',
        'birth_height' => 'nullable|numeric|min:0|max:999.99',
        'birth_weight' => 'nullable|numeric|min:0|max:99.999',
        'birthplace' => 'nullable|string|max:255',
        'mother_name' => 'required|string|max:255|min:2',
        'father_name' => 'nullable|string|max:255|min:2',
        'phone_number' => [
            'required',
            'string',
            'regex:/^(\+63|63|0)?[9]\d{9}$/',
            'min:10',
            'max:15'
        ],
        'address' => 'nullable|string|max:1000'
    ], [
        'child_name.required' => 'Child name is required.',
        'child_name.min' => 'Child name must be at least 2 characters.',
        'mother_name.required' => 'Mother\'s name is required.',
        'mother_name.min' => 'Mother\'s name must be at least 2 characters.',
        'phone_number.required' => 'Phone number is required.',
        'phone_number.regex' => 'Please enter a valid Philippine mobile number (e.g., 09123456789).',
        'birthdate.required' => 'Birth date is required.',
        'birthdate.before_or_equal' => 'Birth date cannot be in the future.',
        'birthdate.after' => 'Please enter a valid birth date.',
        'gender.required' => 'Gender selection is required.',
        'birth_height.numeric' => 'Birth height must be a valid number.',
        'birth_weight.numeric' => 'Birth weight must be a valid number.',
    ]);

    try {
        // Update the record
        $childrecord->update($validated);
        
        // Add debug logging to verify update
        \Log::info('Child record updated successfully', [
            'id' => $childrecord->id,
            'updated_by_role' => $user->role,
            'updated_data' => $validated,
            'record_after_update' => $childrecord->fresh()->toArray()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Child record updated successfully!',
                'data' => $childrecord->fresh()
            ], 200);
        }

        // Role-based redirect
        $redirectRoute = $user->role === 'bhw' 
            ? 'bhw.childrecord.index' 
            : 'midwife.childrecord.index';

        return redirect()->route($redirectRoute)
                        ->with('success', 'Child record updated successfully!');
    } catch (\Exception $e) {
        \Log::error('Error updating child record: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        \Log::error('Request data: ' . json_encode($request->all()));
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating record. Please try again.'
            ], 500);
        }

        return back()->withInput()->withErrors(['error' => 'Error updating record. Please try again.']);
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
            $childName = $childRecord->child_name;
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