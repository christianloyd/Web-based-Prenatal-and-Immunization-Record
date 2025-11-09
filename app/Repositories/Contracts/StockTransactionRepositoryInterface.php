<?php

namespace App\Repositories\Contracts;

use App\Models\StockTransaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface StockTransactionRepositoryInterface
{
    /**
     * Get all transactions
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Find transaction by ID
     *
     * @param int $id
     * @return StockTransaction|null
     */
    public function find(int $id): ?StockTransaction;

    /**
     * Create new transaction
     *
     * @param array $data
     * @return StockTransaction
     */
    public function create(array $data): StockTransaction;

    /**
     * Get paginated transactions
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get transactions by vaccine
     *
     * @param int $vaccineId
     * @return Collection
     */
    public function getByVaccine(int $vaccineId): Collection;

    /**
     * Get transactions by type
     *
     * @param string $type (in/out)
     * @return Collection
     */
    public function getByType(string $type): Collection;

    /**
     * Get transactions by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Get recent transactions
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecent(int $limit = 10): Collection;
}
