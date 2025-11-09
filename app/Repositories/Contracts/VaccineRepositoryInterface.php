<?php

namespace App\Repositories\Contracts;

use App\Models\Vaccine;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface VaccineRepositoryInterface
{
    /**
     * Get all vaccines
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Find vaccine by ID
     *
     * @param int $id
     * @return Vaccine|null
     */
    public function find(int $id): ?Vaccine;

    /**
     * Find vaccine with relationships
     *
     * @param int $id
     * @param array $relations
     * @return Vaccine|null
     */
    public function findWithRelations(int $id, array $relations = []): ?Vaccine;

    /**
     * Create new vaccine
     *
     * @param array $data
     * @return Vaccine
     */
    public function create(array $data): Vaccine;

    /**
     * Update vaccine
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete vaccine
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get paginated vaccines
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get active vaccines (not expired, in stock)
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Get expiring vaccines within specified days
     *
     * @param int $days
     * @return Collection
     */
    public function getExpiring(int $days = 30): Collection;

    /**
     * Get low stock vaccines
     *
     * @param int $threshold
     * @return Collection
     */
    public function getLowStock(int $threshold = 10): Collection;

    /**
     * Get out of stock vaccines
     *
     * @return Collection
     */
    public function getOutOfStock(): Collection;

    /**
     * Search vaccines by name or vaccine code
     *
     * @param string $term
     * @return Collection
     */
    public function search(string $term): Collection;

    /**
     * Update vaccine stock
     *
     * @param int $id
     * @param int $quantity
     * @param string $type (in/out)
     * @return bool
     */
    public function updateStock(int $id, int $quantity, string $type = 'in'): bool;

    /**
     * Get vaccines by disease
     *
     * @param string $disease
     * @return Collection
     */
    public function getByDisease(string $disease): Collection;
}
