<?php

namespace App\Repositories;

use App\Models\StockTransaction;
use App\Repositories\Contracts\StockTransactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StockTransactionRepository implements StockTransactionRepositoryInterface
{
    protected $model;

    public function __construct(StockTransaction $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with('vaccine')->orderBy('created_at', 'desc')->get();
    }

    public function find(int $id): ?StockTransaction
    {
        return $this->model->find($id);
    }

    public function create(array $data): StockTransaction
    {
        return $this->model->create($data);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('vaccine')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getByVaccine(int $vaccineId): Collection
    {
        return $this->model->where('vaccine_id', $vaccineId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByType(string $type): Collection
    {
        return $this->model->where('type', $type)
            ->with('vaccine')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate])
            ->with('vaccine')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getRecent(int $limit = 10): Collection
    {
        return $this->model->with('vaccine')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
