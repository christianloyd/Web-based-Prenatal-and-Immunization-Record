<?php

namespace App\Repositories\Contracts;

use App\Models\SmsLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SmsLogRepositoryInterface
{
    /**
     * Get all SMS logs
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Find SMS log by ID
     *
     * @param int $id
     * @return SmsLog|null
     */
    public function find(int $id): ?SmsLog;

    /**
     * Create new SMS log
     *
     * @param array $data
     * @return SmsLog
     */
    public function create(array $data): SmsLog;

    /**
     * Get paginated SMS logs
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get SMS logs by status
     *
     * @param string $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get recent SMS logs
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecent(int $limit = 50): Collection;

    /**
     * Get SMS logs by phone number
     *
     * @param string $phoneNumber
     * @return Collection
     */
    public function getByPhoneNumber(string $phoneNumber): Collection;

    /**
     * Get SMS logs by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Get failed SMS logs
     *
     * @return Collection
     */
    public function getFailed(): Collection;
}
