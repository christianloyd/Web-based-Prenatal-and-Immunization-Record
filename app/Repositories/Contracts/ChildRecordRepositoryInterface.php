<?php

namespace App\Repositories\Contracts;

use App\Models\ChildRecord;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Child Record Repository Interface
 *
 * Defines contract for child record data access operations
 */
interface ChildRecordRepositoryInterface
{
    /**
     * Get all child records
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Get paginated child records
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    /**
     * Find child record by ID
     *
     * @param int $id
     * @return ChildRecord|null
     */
    public function find(int $id): ?ChildRecord;

    /**
     * Find by formatted ID (e.g., CH-001)
     *
     * @param string $formattedId
     * @return ChildRecord|null
     */
    public function findByFormattedId(string $formattedId): ?ChildRecord;

    /**
     * Create a new child record
     *
     * @param array $data
     * @return ChildRecord
     */
    public function create(array $data): ChildRecord;

    /**
     * Update child record
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete child record (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get child records by mother ID
     *
     * @param int $motherId
     * @return Collection
     */
    public function getByMother(int $motherId): Collection;

    /**
     * Get children due for vaccination
     *
     * @param int $daysAhead Number of days to look ahead
     * @return Collection
     */
    public function getDueForVaccination(int $daysAhead = 7): Collection;

    /**
     * Get children with missed vaccinations
     *
     * @return Collection
     */
    public function getWithMissedVaccinations(): Collection;

    /**
     * Get children by age range (in months)
     *
     * @param int $minMonths
     * @param int $maxMonths
     * @return Collection
     */
    public function getByAgeRange(int $minMonths, int $maxMonths): Collection;

    /**
     * Get child with full profile (all relationships)
     *
     * @param int $id
     * @return ChildRecord|null
     */
    public function getFullProfile(int $id): ?ChildRecord;

    /**
     * Search child records
     *
     * @param string $term
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $term, int $perPage = 20): LengthAwarePaginator;

    /**
     * Count total children
     *
     * @return int
     */
    public function count(): int;

    /**
     * Count children under specific age (in years)
     *
     * @param int $years
     * @return int
     */
    public function countUnderAge(int $years = 5): int;
}
