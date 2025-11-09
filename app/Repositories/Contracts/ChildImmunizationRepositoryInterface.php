<?php

namespace App\Repositories\Contracts;

use App\Models\ChildImmunization;
use Illuminate\Support\Collection;

interface ChildImmunizationRepositoryInterface
{
    /**
     * Get all child immunization records
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Find child immunization by ID
     *
     * @param int $id
     * @return ChildImmunization|null
     */
    public function find(int $id): ?ChildImmunization;

    /**
     * Create new child immunization record
     *
     * @param array $data
     * @return ChildImmunization
     */
    public function create(array $data): ChildImmunization;

    /**
     * Update child immunization
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete child immunization
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get immunizations by child record
     *
     * @param int $childRecordId
     * @return Collection
     */
    public function getByChildRecord(int $childRecordId): Collection;

    /**
     * Get immunizations by vaccine
     *
     * @param int $vaccineId
     * @return Collection
     */
    public function getByVaccine(int $vaccineId): Collection;
}
