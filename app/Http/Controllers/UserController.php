<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserService;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Utils\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userRepository;
    protected $userService;

    /**
     * Constructor - Inject User Repository and Service
     */
    public function __construct(UserRepositoryInterface $userRepository, UserService $userService)
    {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
    }

    /**
     * Check if user is authorized (midwife only)
     */
    private function checkAuthorization()
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        if (Auth::user()->role !== 'midwife') {
            abort(403, 'Unauthorized access. Only Midwives can manage users.');
        }
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $this->checkAuthorization();

        try {
            // Build filters array
            $filters = [];
            if ($request->filled('search')) {
                $filters['search'] = $request->search;
            }
            if ($request->filled('role')) {
                $filters['role'] = $request->role;
            }
            if ($request->filled('gender')) {
                $filters['gender'] = $request->gender;
            }
            if ($request->filled('status')) {
                $filters['status'] = $request->status;
            }

            // Build sorting params
            $sortField = in_array($request->get('sort', 'name'), ['name', 'username', 'role', 'created_at', 'is_active'])
                ? $request->get('sort', 'name')
                : 'name';
            $sortDirection = $request->get('direction', 'asc');

            // Use repository to get paginated users
            $users = $this->userRepository->getAllPaginated($filters, $sortField, $sortDirection, 15);

            return view('midwife.user.index', compact('users'));

        } catch (\Exception $e) {
            Log::error('Error loading users', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error loading users: ' . $e->getMessage());
        }
    }
    
    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $this->checkAuthorization();
        return view('midwife.user.create');
    }

    /**
     * Store a newly created user
     */
    public function store(StoreUserRequest $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                // Validation is handled by StoreUserRequest
                // Create user using service (handles password hashing)
                $newUser = $this->userService->createUser($request->validated());

                if ($request->expectsJson()) {
                    return ResponseHelper::success($newUser, 'User created successfully!', 201);
                }

                return redirect()->route('midwife.user.index')
                    ->with('success', 'User "' . $newUser->name . '" has been successfully created.');

            } catch (\Exception $e) {
                Log::error('Error creating user', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'input' => $request->validated()
                ]);

                if ($request->expectsJson()) {
                    return ResponseHelper::error($e->getMessage(), [], 500);
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Error creating user: ' . $e->getMessage());
            }
        });
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
                           ->with('success', 'User "' . $user->name . '" has been successfully updated.');

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

            $userName = $user->name;
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

            $userName = $user->name;
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

            $userName = $user->name;
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
            $users = User::select('id', 'name', 'username', 'role')
                        ->where('id', '!=', Auth::id())
                        ->orderBy('name')
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