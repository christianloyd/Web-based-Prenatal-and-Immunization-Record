<?php

namespace App\Repositories\Contracts;

use App\Models\PrenatalRecord;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Prenatal Record Repository Interface
 *
 * Defines contract for prenatal record data access operations
 */
interface PrenatalRecordRepositoryInterface
{
    /**
     * Get all prenatal records
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Get paginated prenatal records
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    /**
     * Find prenatal record by ID
     *
     * @param int $id
     * @return PrenatalRecord|null
     */
    public function find(int $id): ?PrenatalRecord;

    /**
     * Create a new prenatal record
     *
     * @param array $data
     * @return PrenatalRecord
     */
    public function create(array $data): PrenatalRecord;

    /**
     * Update prenatal record
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete prenatal record (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get active prenatal records
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Get active prenatal records paginated
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActivePaginated(int $perPage = 20): LengthAwarePaginator;

    /**
     * Get prenatal records by status
     *
     * @param string $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get prenatal records by patient ID
     *
     * @param int $patientId
     * @return Collection
     */
    public function getByPatient(int $patientId): Collection;

    /**
     * Get active prenatal record for patient
     *
     * @param int $patientId
     * @return PrenatalRecord|null
     */
    public function getActiveByPatient(int $patientId): ?PrenatalRecord;

    /**
     * Get high-risk prenatal records
     *
     * @return Collection
     */
    public function getHighRisk(): Collection;

    /**
     * Get overdue pregnancies
     *
     * @return Collection
     */
    public function getOverdue(): Collection;

    /**
     * Complete pregnancy (set status to completed)
     *
     * @param int $id
     * @return bool
     */
    public function completePregnancy(int $id): bool;

    /**
     * Update gestational age for all active records
     *
     * @return int Number of records updated
     */
    public function updateGestationalAges(): int;

    /**
     * Search prenatal records
     *
     * @param string $term
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $term, int $perPage = 20): LengthAwarePaginator;
}
