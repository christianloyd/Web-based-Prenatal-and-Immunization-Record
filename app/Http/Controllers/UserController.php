<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        // Check authorization first
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $user = Auth::user();
        
        // Only Midwives can access user management
        if ($user->role !== 'midwife') {
            abort(403, 'Unauthorized access. Only Midwives can manage users.');
        }

        try {
            $query = User::query();

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->search($search);
            }

            // Apply role filter
            if ($request->filled('role')) {
                $query->byRole($request->get('role'));
            }

            // Apply gender filter
            if ($request->filled('gender')) {
                $query->byGender($request->get('gender'));
            }

            // Apply status filter
            if ($request->filled('status')) {
                $query->byStatus($request->get('status'));
            }

            // Apply sorting
            $sortField = $request->get('sort', 'full_name');
            $sortDirection = $request->get('direction', 'asc');

            if (in_array($sortField, ['full_name', 'username', 'role', 'created_at', 'is_active'])) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                $query->orderBy('full_name', 'asc');
            }

            // Paginate results
            $users = $query->paginate(15)->withQueryString();

            return view('midwife.user.index', compact('users'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading users: ' . $e->getMessage());
        }
    }
    /**
     * Store a newly created user
     */
    public function store(Request $request)
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
        
        // Only Midwives can create users
        if ($user->role !== 'midwife') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            abort(403, 'Unauthorized access');
        }

        try {
            // Validate the request
            $validated = $request->validate(
                User::validationRules(),
                User::validationMessages()
            );

            // Hash the password
            $validated['password'] = Hash::make($validated['password']);

            // Create the user
            $newUser = User::create($validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User created successfully!',
                    'data' => $newUser
                ], 201);
            }

            return redirect()->route('midwife.user.index')
                           ->with('success', 'User "' . $newUser->full_name . '" has been successfully created.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                           ->withErrors($e->validator)
                           ->withInput()
                           ->with('error', 'Please correct the validation errors.');
        } catch (\Exception $e) {
            \Log::error('Error creating user: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating user. Please try again.'
                ], 500);
            }

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error creating user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        // Check authorization
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.'
            ], 401);
        }

        $currentUser = Auth::user();
        
        // Only Midwives can view user details
        if ($currentUser->role !== 'midwife') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        try {
            return response()->json([
                'success' => true,
                'user' => $user->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading user details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
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

        $currentUser = Auth::user();
        
        // Only Midwives can update users
        if ($currentUser->role !== 'midwife') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            abort(403, 'Unauthorized access');
        }

        try {
            // Validate the request with user-specific rules
            $validated = $request->validate(
                User::updateValidationRules($user->id),
                User::validationMessages()
            );

            // Handle password update
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                // Remove password from validated data if empty
                unset($validated['password']);
            }

            // Update the user
            $user->update($validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully!',
                    'data' => $user->fresh()
                ], 200);
            }

            return redirect()->route('midwife.user.index')
                           ->with('success', 'User "' . $user->full_name . '" has been successfully updated.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                           ->withErrors($e->validator, 'edit_errors')
                           ->withInput()
                           ->with('error', 'Please correct the validation errors.');
        } catch (\Exception $e) {
            \Log::error('Error updating user: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating user. Please try again.'
                ], 500);
            }

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error updating user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
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

        $currentUser = Auth::user();
        
        // Only Midwives can delete users
        if ($currentUser->role !== 'midwife') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            abort(403, 'Unauthorized access');
        }

        try {
            // Prevent deletion of the current user
            if ($user->id === Auth::id()) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot delete your own account.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'You cannot delete your own account.');
            }

            // Prevent deletion if it's the only Midwife
            if ($user->role === 'midwife') {
                $midwifeCount = User::where('role', 'midwife')->count();
                if ($midwifeCount <= 1) {
                    if (request()->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot delete the last Midwife account.'
                        ], 422);
                    }
                    return redirect()->back()->with('error', 'Cannot delete the last Midwife account.');
                }
            }

            $userName = $user->full_name;
            $user->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "User \"{$userName}\" has been deleted successfully!"
                ]);
            }

            return redirect()->route('midwife.user.index')
                           ->with('success', 'User "' . $userName . '" has been successfully deleted.');

        } catch (\Exception $e) {
            \Log::error('Error deleting user: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting user. Please try again.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }
    
    public function deactivate(User $user)
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

        $currentUser = Auth::user();
        
        // Only Midwives can deactivate users
        if ($currentUser->role !== 'midwife') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            abort(403, 'Unauthorized access');
        }

        try {
            // Prevent deactivation of the current user
            if ($user->id === Auth::id()) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot deactivate your own account.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'You cannot deactivate your own account.');
            }

            // Prevent deactivation if it's the only active Midwife
            if ($user->role === 'midwife' && $user->is_active) {
                $activeMidwifeCount = User::where('role', 'midwife')->where('is_active', true)->count();
                if ($activeMidwifeCount <= 1) {
                    if (request()->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot deactivate the last active Midwife account.'
                        ], 422);
                    }
                    return redirect()->back()->with('error', 'Cannot deactivate the last active Midwife account.');
                }
            }

            // Check if user is already inactive
            if (!$user->is_active) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User is already inactive.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'User is already inactive.');
            }

            $userName = $user->full_name;
            $user->update(['is_active' => false]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "User \"{$userName}\" has been deactivated successfully!"
                ]);
            }

            return redirect()->route('midwife.user.index')
                           ->with('success', 'User "' . $userName . '" has been successfully deactivated.');

        } catch (\Exception $e) {
            \Log::error('Error deactivating user: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deactivating user. Please try again.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Error deactivating user: ' . $e->getMessage());
        }
    }

    /**
     * Activate the specified user
     */
    public function activate(User $user)
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

        $currentUser = Auth::user();
        
        // Only Midwives can activate users
        if ($currentUser->role !== 'midwife') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            abort(403, 'Unauthorized access');
        }

        try {
            // Check if user is already active
            if ($user->is_active) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User is already active.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'User is already active.');
            }

            $userName = $user->full_name;
            $user->update(['is_active' => true]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "User \"{$userName}\" has been activated successfully!"
                ]);
            }

            return redirect()->route('midwife.user.index')
                           ->with('success', 'User "' . $userName . '" has been successfully activated.');

        } catch (\Exception $e) {
            \Log::error('Error activating user: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error activating user. Please try again.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Error activating user: ' . $e->getMessage());
        }
    }
    /**
     * Check username availability
     */
    public function checkUsername(Request $request)
    {
        // Check authorization
        if (!Auth::check()) {
            return response()->json([
                'available' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $user = Auth::user();
        
        // Only Midwives can check usernames
        if ($user->role !== 'midwife') {
            return response()->json([
                'available' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $username = $request->get('username');
        $userId = $request->get('user_id');

        $query = User::where('username', $username);
        
        if ($userId) {
            $query->where('id', '!=', $userId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Username is already taken' : 'Username is available'
        ]);
    }

    /**
     * Get users for select dropdown (excluding current user)
     */
    public function getUsersForSelect()
    {
        // Check authorization
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.'
            ], 401);
        }

        $user = Auth::user();
        
        // Only Midwives can get user lists
        if ($user->role !== 'midwife') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        try {
            $users = User::select('id', 'full_name', 'username', 'role')
                        ->where('id', '!=', Auth::id())
                        ->orderBy('full_name')
                        ->get();

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading users: ' . $e->getMessage()
            ], 500);
        }
    }

    
}