<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Password is already hashed in the repository
            $user = $this->userRepository->create($data);

            Log::info('User created', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'created_by' => Auth::id(),
            ]);

            return $user;
        });
    }

    /**
     * Update user
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            // Remove password if empty (not being updated)
            if (isset($data['password']) && empty($data['password'])) {
                unset($data['password']);
            }

            $result = $this->userRepository->update($id, $data);

            if ($result) {
                Log::info('User updated', [
                    'user_id' => $id,
                    'updated_by' => Auth::id(),
                ]);
            }

            return $result;
        });
    }

    /**
     * Delete user
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteUser(int $id): bool
    {
        // Prevent deleting own account
        if (Auth::id() === $id) {
            throw new \Exception('Cannot delete your own account');
        }

        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new \Exception('User not found');
        }

        return DB::transaction(function () use ($user) {
            $result = $this->userRepository->delete($user->id);

            if ($result) {
                Log::info('User deleted', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'deleted_by' => Auth::id(),
                ]);
            }

            return $result;
        });
    }

    /**
     * Toggle user active status
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function toggleActiveStatus(int $id): bool
    {
        // Prevent deactivating own account
        if (Auth::id() === $id) {
            throw new \Exception('Cannot deactivate your own account');
        }

        return DB::transaction(function () use ($id) {
            $result = $this->userRepository->toggleActiveStatus($id);

            if ($result) {
                $user = $this->userRepository->find($id);
                Log::info('User status toggled', [
                    'user_id' => $id,
                    'new_status' => $user->is_active ? 'active' : 'inactive',
                    'changed_by' => Auth::id(),
                ]);
            }

            return $result;
        });
    }

    /**
     * Change user password
     *
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(int $userId, string $newPassword): bool
    {
        return DB::transaction(function () use ($userId, $newPassword) {
            $result = $this->userRepository->update($userId, [
                'password' => Hash::make($newPassword),
            ]);

            if ($result) {
                Log::info('User password changed', [
                    'user_id' => $userId,
                    'changed_by' => Auth::id(),
                ]);
            }

            return $result;
        });
    }

    /**
     * Get healthcare workers (midwives and BHWs)
     *
     * @return \Illuminate\Support\Collection
     */
    public function getHealthcareWorkers()
    {
        return $this->userRepository->getHealthcareWorkers();
    }

    /**
     * Get users by role
     *
     * @param string $role
     * @return \Illuminate\Support\Collection
     */
    public function getUsersByRole(string $role)
    {
        return $this->userRepository->getByRole($role);
    }

    /**
     * Get active users
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveUsers()
    {
        return $this->userRepository->getActive();
    }

    /**
     * Search users
     *
     * @param string $term
     * @return \Illuminate\Support\Collection
     */
    public function searchUsers(string $term)
    {
        return $this->userRepository->search($term);
    }

    /**
     * Get user statistics
     *
     * @return array
     */
    public function getUserStatistics(): array
    {
        $allUsers = $this->userRepository->all();

        return [
            'total_users' => $allUsers->count(),
            'active_users' => $allUsers->where('is_active', true)->count(),
            'inactive_users' => $allUsers->where('is_active', false)->count(),
            'midwives' => $allUsers->where('role', 'midwife')->count(),
            'bhws' => $allUsers->where('role', 'bhw')->count(),
            'active_midwives' => $allUsers->where('role', 'midwife')->where('is_active', true)->count(),
            'active_bhws' => $allUsers->where('role', 'bhw')->where('is_active', true)->count(),
        ];
    }

    /**
     * Validate unique email
     *
     * @param string $email
     * @param int|null $excludeUserId
     * @return bool
     */
    public function isEmailUnique(string $email, ?int $excludeUserId = null): bool
    {
        $users = $this->userRepository->all();

        foreach ($users as $user) {
            if ($excludeUserId && $user->id === $excludeUserId) {
                continue;
            }

            if (strtolower($user->email) === strtolower($email)) {
                return false;
            }
        }

        return true;
    }
}
