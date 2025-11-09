<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    /**
     * Get all users
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Find user by ID
     *
     * @param int $id
     * @return User|null
     */
    public function find(int $id): ?User;

    /**
     * Find user with relationships
     *
     * @param int $id
     * @param array $relations
     * @return User|null
     */
    public function findWithRelations(int $id, array $relations = []): ?User;

    /**
     * Create new user
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * Update user
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete user
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get paginated users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get users by role
     *
     * @param string $role
     * @return Collection
     */
    public function getByRole(string $role): Collection;

    /**
     * Get active users
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Get healthcare workers (midwives and BHWs)
     *
     * @return Collection
     */
    public function getHealthcareWorkers(): Collection;

    /**
     * Search users by name or email
     *
     * @param string $term
     * @return Collection
     */
    public function search(string $term): Collection;

    /**
     * Toggle user active status
     *
     * @param int $id
     * @return bool
     */
    public function toggleActiveStatus(int $id): bool;
}
