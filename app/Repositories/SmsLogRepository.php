<?php

namespace App\Repositories;

use App\Models\SmsLog;
use App\Repositories\Contracts\SmsLogRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SmsLogRepository implements SmsLogRepositoryInterface
{
    protected $model;

    public function __construct(SmsLog $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->orderBy('created_at', 'desc')->get();
    }

    public function find(int $id): ?SmsLog
    {
        return $this->model->find($id);
    }

    public function create(array $data): SmsLog
    {
        return $this->model->create($data);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getRecent(int $limit = 50): Collection
    {
        return $this->model->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getByPhoneNumber(string $phoneNumber): Collection
    {
        return $this->model->where('phone_number', $phoneNumber)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getFailed(): Collection
    {
        return $this->model->where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
