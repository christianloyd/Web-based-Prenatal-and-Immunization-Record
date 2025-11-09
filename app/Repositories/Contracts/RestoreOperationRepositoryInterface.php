<?php

namespace App\Repositories\Contracts;

use App\Models\RestoreOperation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RestoreOperationRepositoryInterface
{
    /**
     * Get all restore operations
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Find restore operation by ID
     *
     * @param int $id
     * @return RestoreOperation|null
     */
    public function find(int $id): ?RestoreOperation;

    /**
     * Create new restore operation record
     *
     * @param array $data
     * @return RestoreOperation
     */
    public function create(array $data): RestoreOperation;

    /**
     * Update restore operation
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Get paginated restore operations
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get successful restore operations
     *
     * @return Collection
     */
    public function getSuccessful(): Collection;

    /**
     * Get failed restore operations
     *
     * @return Collection
     */
    public function getFailed(): Collection;

    /**
     * Get restore operations by user
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUser(int $userId): Collection;

    /**
     * Get latest restore operation
     *
     * @return RestoreOperation|null
     */
    public function getLatest(): ?RestoreOperation;
}
