<?php

namespace App\Repositories;

use App\Models\RestoreOperation;
use App\Repositories\Contracts\RestoreOperationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RestoreOperationRepository implements RestoreOperationRepositoryInterface
{
    protected $model;

    public function __construct(RestoreOperation $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with('user')->orderBy('created_at', 'desc')->get();
    }

    public function find(int $id): ?RestoreOperation
    {
        return $this->model->find($id);
    }

    public function create(array $data): RestoreOperation
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $operation = $this->find($id);
        return $operation ? $operation->update($data) : false;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getSuccessful(): Collection
    {
        return $this->model->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getFailed(): Collection
    {
        return $this->model->where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLatest(): ?RestoreOperation
    {
        return $this->model->orderBy('created_at', 'desc')->first();
    }
}
