<?php

namespace App\Repositories\Contracts;

use App\Models\CloudBackup;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CloudBackupRepositoryInterface
{
    /**
     * Get all cloud backups
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Find backup by ID
     *
     * @param int $id
     * @return CloudBackup|null
     */
    public function find(int $id): ?CloudBackup;

    /**
     * Create new backup record
     *
     * @param array $data
     * @return CloudBackup
     */
    public function create(array $data): CloudBackup;

    /**
     * Update backup
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete backup record
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get paginated backups
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get latest backup
     *
     * @return CloudBackup|null
     */
    public function getLatest(): ?CloudBackup;

    /**
     * Get successful backups
     *
     * @return Collection
     */
    public function getSuccessful(): Collection;

    /**
     * Get failed backups
     *
     * @return Collection
     */
    public function getFailed(): Collection;

    /**
     * Get backups by user
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUser(int $userId): Collection;
}
