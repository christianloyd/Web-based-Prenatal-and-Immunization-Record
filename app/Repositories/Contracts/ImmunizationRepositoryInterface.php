<?php

namespace App\Repositories\Contracts;

use App\Models\Immunization;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ImmunizationRepositoryInterface
{
    /**
     * Get all immunizations
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Find immunization by ID
     *
     * @param int $id
     * @return Immunization|null
     */
    public function find(int $id): ?Immunization;

    /**
     * Find immunization with relationships
     *
     * @param int $id
     * @param array $relations
     * @return Immunization|null
     */
    public function findWithRelations(int $id, array $relations = []): ?Immunization;

    /**
     * Create new immunization schedule
     *
     * @param array $data
     * @return Immunization
     */
    public function create(array $data): Immunization;

    /**
     * Update immunization
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete immunization
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get paginated immunizations
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get immunizations by status
     *
     * @param string $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get upcoming immunizations (next 7 days)
     *
     * @param int $days
     * @return Collection
     */
    public function getUpcoming(int $days = 7): Collection;

    /**
     * Get overdue/missed immunizations
     *
     * @return Collection
     */
    public function getOverdue(): Collection;

    /**
     * Get immunizations by child record
     *
     * @param int $childRecordId
     * @return Collection
     */
    public function getByChildRecord(int $childRecordId): Collection;

    /**
     * Mark immunization as done
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function markAsDone(int $id, array $data = []): bool;

    /**
     * Mark immunization as missed
     *
     * @param int $id
     * @param string $reason
     * @return bool
     */
    public function markAsMissed(int $id, string $reason): bool;

    /**
     * Reschedule immunization
     *
     * @param int $id
     * @param string $newDate
     * @param string|null $newTime
     * @return bool
     */
    public function reschedule(int $id, string $newDate, ?string $newTime = null): bool;

    /**
     * Search immunizations
     *
     * @param string $term
     * @return Collection
     */
    public function search(string $term): Collection;
}
