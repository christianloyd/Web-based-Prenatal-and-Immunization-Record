<?php

namespace App\Repositories\Contracts;

use App\Models\PrenatalVisit;
use Illuminate\Support\Collection;

interface PrenatalVisitRepositoryInterface
{
    /**
     * Get all prenatal visits
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Find visit by ID
     *
     * @param int $id
     * @return PrenatalVisit|null
     */
    public function find(int $id): ?PrenatalVisit;

    /**
     * Create new visit
     *
     * @param array $data
     * @return PrenatalVisit
     */
    public function create(array $data): PrenatalVisit;

    /**
     * Update visit
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete visit
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get visits by prenatal record
     *
     * @param int $prenatalRecordId
     * @return Collection
     */
    public function getByPrenatalRecord(int $prenatalRecordId): Collection;

    /**
     * Get visits by patient
     *
     * @param int $patientId
     * @return Collection
     */
    public function getByPatient(int $patientId): Collection;
}
