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
        $this->checkAuthorization();

        try {
            return ResponseHelper::success($user->toArray());
        } catch (\Exception $e) {
            Log::error('Error loading user details', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return ResponseHelper::error('Error loading user details: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        return DB::transaction(function () use ($request, $user) {
            try {
                // Validation is handled by UpdateUserRequest
                // Update user using service (handles password hashing)
                $updatedUser = $this->userService->updateUser($user->id, $request->validated());

                if ($request->expectsJson()) {
                    return ResponseHelper::success($updatedUser, 'User updated successfully!');
                }

                return redirect()->route('midwife.user.index')
                    ->with('success', 'User "' . $updatedUser->name . '" has been successfully updated.');

            } catch (\Exception $e) {
                Log::error('Error updating user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'input' => $request->validated()
                ]);

                if ($request->expectsJson()) {
                    return ResponseHelper::error($e->getMessage(), [], 500);
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Error updating user: ' . $e->getMessage());
            }
        });
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        $this->checkAuthorization();

        return DB::transaction(function () use ($user) {
            try {
                // Use service to delete (handles safety checks: can't delete self, can't delete last midwife)
                $userName = $this->userService->deleteUser($user->id);

                if (request()->expectsJson()) {
                    return ResponseHelper::success(null, "User \"{$userName}\" has been deleted successfully!");
                }

                return redirect()->route('midwife.user.index')
                    ->with('success', 'User "' . $userName . '" has been successfully deleted.');

            } catch (\Exception $e) {
                Log::error('Error deleting user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);

                if (request()->expectsJson()) {
                    return ResponseHelper::error($e->getMessage(), [], 422);
                }

                return redirect()->back()->with('error', $e->getMessage());
            }
        });
    }
    
    /**
     * Deactivate the specified user
     */
    public function deactivate(User $user)
    {
        $this->checkAuthorization();

        return DB::transaction(function () use ($user) {
            try {
                // Use service to toggle status (handles safety checks)
                $updatedUser = $this->userService->toggleActiveStatus($user->id);
                $userName = $updatedUser->name;

                if (request()->expectsJson()) {
                    return ResponseHelper::success($updatedUser, "User \"{$userName}\" has been deactivated successfully!");
                }

                return redirect()->route('midwife.user.index')
                    ->with('success', 'User "' . $userName . '" has been successfully deactivated.');

            } catch (\Exception $e) {
                Log::error('Error deactivating user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);

                if (request()->expectsJson()) {
                    return ResponseHelper::error($e->getMessage(), [], 422);
                }

                return redirect()->back()->with('error', $e->getMessage());
            }
        });
    }

    /**
     * Activate the specified user
     */
    public function activate(User $user)
    {
        $this->checkAuthorization();

        return DB::transaction(function () use ($user) {
            try {
                // Use service to toggle status
                $updatedUser = $this->userService->toggleActiveStatus($user->id);
                $userName = $updatedUser->name;

                if (request()->expectsJson()) {
                    return ResponseHelper::success($updatedUser, "User \"{$userName}\" has been activated successfully!");
                }

                return redirect()->route('midwife.user.index')
                    ->with('success', 'User "' . $userName . '" has been successfully activated.');

            } catch (\Exception $e) {
                Log::error('Error activating user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);

                if (request()->expectsJson()) {
                    return ResponseHelper::error($e->getMessage(), [], 422);
                }

                return redirect()->back()->with('error', $e->getMessage());
            }
        });
    }
    /**
     * Check username availability
     */
    public function checkUsername(Request $request)
    {
        $this->checkAuthorization();

        try {
            $username = $request->get('username');
            $userId = $request->get('user_id');

            // Check if username exists (excluding specified user ID if provided)
            $exists = $this->userRepository->usernameExists($username, $userId);

            return response()->json([
                'available' => !$exists,
                'message' => $exists ? 'Username is already taken' : 'Username is available'
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking username availability', [
                'username' => $request->get('username'),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'available' => false,
                'message' => 'Error checking username availability'
            ], 500);
        }
    }

    /**
     * Get users for select dropdown (excluding current user)
     */
    public function getUsersForSelect()
    {
        $this->checkAuthorization();

        try {
            // Use repository to get users excluding current user
            $users = $this->userRepository->getAllExcludingUser(Auth::id(), ['id', 'name', 'username', 'role']);

            return ResponseHelper::success($users);
        } catch (\Exception $e) {
            Log::error('Error loading users for select', ['error' => $e->getMessage()]);
            return ResponseHelper::error('Error loading users: ' . $e->getMessage(), [], 500);
        }
    }
}