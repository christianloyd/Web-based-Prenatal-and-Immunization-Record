<?php

namespace App\Repositories;

use App\Models\ChildRecord;
use App\Repositories\Contracts\ChildRecordRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Child Record Repository Implementation
 *
 * Handles all child record data access operations
 */
class ChildRecordRepository implements ChildRecordRepositoryInterface
{
    protected $model;

    /**
     * Constructor
     *
     * @param ChildRecord $model
     */
    public function __construct(ChildRecord $model)
    {
        $this->model = $model;
    }

    /**
     * Get all child records
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->with('mother')->get();
    }

    /**
     * Get paginated child records
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->with('mother')
            ->orderBy('birthdate', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find child record by ID
     *
     * @param int $id
     * @return ChildRecord|null
     */
    public function find(int $id): ?ChildRecord
    {
        return $this->model->with('mother')->find($id);
    }

    /**
     * Find by formatted ID (e.g., CH-001)
     *
     * @param string $formattedId
     * @return ChildRecord|null
     */
    public function findByFormattedId(string $formattedId): ?ChildRecord
    {
        return $this->model->where('formatted_child_id', $formattedId)
            ->with('mother')
            ->first();
    }

    /**
     * Create a new child record
     *
     * @param array $data
     * @return ChildRecord
     */
    public function create(array $data): ChildRecord
    {
        $child = $this->model->create($data);
        $this->clearCache();
        return $child->load('mother');
    }

    /**
     * Update child record
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $child = $this->model->find($id);

        if (!$child) {
            return false;
        }

        $updated = $child->update($data);
        $this->clearCache();

        return $updated;
    }

    /**
     * Delete child record (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $child = $this->model->find($id);

        if (!$child) {
            return false;
        }

        $deleted = $child->delete();
        $this->clearCache();

        return $deleted;
    }

    /**
     * Get child records by mother ID
     *
     * @param int $motherId
     * @return Collection
     */
    public function getByMother(int $motherId): Collection
    {
        return $this->model->where('mother_id', $motherId)
            ->with('immunizations')
            ->orderBy('birthdate', 'desc')
            ->get();
    }

    /**
     * Get children due for vaccination
     *
     * @param int $daysAhead Number of days to look ahead
     * @return Collection
     */
    public function getDueForVaccination(int $daysAhead = 7): Collection
    {
        $endDate = Carbon::now()->addDays($daysAhead);

        return $this->model->whereHas('immunizations', function($query) use ($endDate) {
            $query->where('status', 'upcoming')
                  ->whereDate('schedule_date', '<=', $endDate)
                  ->whereDate('schedule_date', '>=', Carbon::now());
        })
        ->with(['mother', 'immunizations' => function($query) use ($endDate) {
            $query->where('status', 'upcoming')
                  ->whereDate('schedule_date', '<=', $endDate)
                  ->orderBy('schedule_date', 'asc');
        }])
        ->get();
    }

    /**
     * Get children with missed vaccinations
     *
     * @return Collection
     */
    public function getWithMissedVaccinations(): Collection
    {
        return $this->model->whereHas('immunizations', function($query) {
            $query->where('status', 'missed');
        })
        ->with(['mother', 'immunizations' => function($query) {
            $query->where('status', 'missed')
                  ->orderBy('schedule_date', 'desc');
        }])
        ->get();
    }

    /**
     * Get children by age range (in months)
     *
     * @param int $minMonths
     * @param int $maxMonths
     * @return Collection
     */
    public function getByAgeRange(int $minMonths, int $maxMonths): Collection
    {
        $minDate = Carbon::now()->subMonths($maxMonths);
        $maxDate = Carbon::now()->subMonths($minMonths);

        return $this->model->whereBetween('birthdate', [$minDate, $maxDate])
            ->with('mother')
            ->orderBy('birthdate', 'desc')
            ->get();
    }

    /**
     * Get child with full profile (all relationships)
     *
     * @param int $id
     * @return ChildRecord|null
     */
    public function getFullProfile(int $id): ?ChildRecord
    {
        return $this->model->with([
            'mother',
            'mother.prenatalRecords',
            'immunizations' => function($query) {
                $query->orderBy('schedule_date', 'asc');
            },
            'immunizations.vaccine'
        ])->find($id);
    }

    /**
     * Search child records
     *
     * @param string $term
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $term, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->where(function($query) use ($term) {
            $query->where('first_name', 'LIKE', "%{$term}%")
                  ->orWhere('last_name', 'LIKE', "%{$term}%")
                  ->orWhere('formatted_child_id', 'LIKE', "%{$term}%")
                  ->orWhereHas('mother', function($q) use ($term) {
                      $q->where('first_name', 'LIKE', "%{$term}%")
                        ->orWhere('last_name', 'LIKE', "%{$term}%");
                  });
        })
        ->with('mother')
        ->orderBy('birthdate', 'desc')
        ->paginate($perPage);
    }

    /**
     * Count total children
     *
     * @return int
     */
    public function count(): int
    {
        return Cache::remember('children_count', 600, function() {
            return $this->model->count();
        });
    }

    /**
     * Count children under specific age (in years)
     *
     * @param int $years
     * @return int
     */
    public function countUnderAge(int $years = 5): int
    {
        $date = Carbon::now()->subYears($years);

        return Cache::remember("children_under_{$years}_count", 600, function() use ($date) {
            return $this->model->where('birthdate', '>=', $date)->count();
        });
    }

    /**
     * Clear child record related caches
     *
     * @return void
     */
    protected function clearCache(): void
    {
        Cache::forget('children_count');
        for ($i = 1; $i <= 10; $i++) {
            Cache::forget("children_under_{$i}_count");
        }
        Cache::forget('dashboard_stats');
    }
}
