<?php

namespace App\Repositories\Contracts;

use App\Models\PrenatalCheckup;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PrenatalCheckupRepositoryInterface
{
    /**
     * Get all checkups
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Find checkup by ID
     *
     * @param int $id
     * @return PrenatalCheckup|null
     */
    public function find(int $id): ?PrenatalCheckup;

    /**
     * Find checkup with relationships
     *
     * @param int $id
     * @param array $relations
     * @return PrenatalCheckup|null
     */
    public function findWithRelations(int $id, array $relations = []): ?PrenatalCheckup;

    /**
     * Create new checkup
     *
     * @param array $data
     * @return PrenatalCheckup
     */
    public function create(array $data): PrenatalCheckup;

    /**
     * Update checkup
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete checkup
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get paginated checkups
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get checkups by patient
     *
     * @param int $patientId
     * @return Collection
     */
    public function getByPatient(int $patientId): Collection;

    /**
     * Get checkups by prenatal record
     *
     * @param int $prenatalRecordId
     * @return Collection
     */
    public function getByPrenatalRecord(int $prenatalRecordId): Collection;

    /**
     * Get latest checkup for patient
     *
     * @param int $patientId
     * @return PrenatalCheckup|null
     */
    public function getLatestForPatient(int $patientId): ?PrenatalCheckup;

    /**
     * Get upcoming checkups
     *
     * @param int $days
     * @return Collection
     */
    public function getUpcoming(int $days = 7): Collection;

    /**
     * Get overdue checkups
     *
     * @return Collection
     */
    public function getOverdue(): Collection;

    /**
     * Get checkups by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Search checkups
     *
     * @param string $term
     * @return Collection
     */
    public function search(string $term): Collection;
}
