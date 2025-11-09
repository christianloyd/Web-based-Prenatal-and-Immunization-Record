<?php

namespace App\Repositories;

use App\Models\CloudBackup;
use App\Repositories\Contracts\CloudBackupRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CloudBackupRepository implements CloudBackupRepositoryInterface
{
    protected $model;

    public function __construct(CloudBackup $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with('user')->orderBy('created_at', 'desc')->get();
    }

    public function find(int $id): ?CloudBackup
    {
        return $this->model->find($id);
    }

    public function create(array $data): CloudBackup
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $backup = $this->find($id);
        return $backup ? $backup->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $backup = $this->find($id);
        return $backup ? $backup->delete() : false;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getLatest(): ?CloudBackup
    {
        return $this->model->orderBy('created_at', 'desc')->first();
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
}
