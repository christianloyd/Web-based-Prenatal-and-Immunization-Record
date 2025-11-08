<?php

namespace App\Repositories;

use App\Models\PrenatalRecord;
use App\Repositories\Contracts\PrenatalRecordRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

/**
 * Prenatal Record Repository Implementation
 *
 * Handles all prenatal record data access operations
 */
class PrenatalRecordRepository implements PrenatalRecordRepositoryInterface
{
    protected $model;

    /**
     * Constructor
     *
     * @param PrenatalRecord $model
     */
    public function __construct(PrenatalRecord $model)
    {
        $this->model = $model;
    }

    /**
     * Get all prenatal records
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->with('patient')->get();
    }

    /**
     * Get paginated prenatal records
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->with('patient')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find prenatal record by ID
     *
     * @param int $id
     * @return PrenatalRecord|null
     */
    public function find(int $id): ?PrenatalRecord
    {
        return $this->model->with('patient')->find($id);
    }

    /**
     * Create a new prenatal record
     *
     * @param array $data
     * @return PrenatalRecord
     */
    public function create(array $data): PrenatalRecord
    {
        $record = $this->model->create($data);
        $this->clearCache();
        return $record->load('patient');
    }

    /**
     * Update prenatal record
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $record = $this->model->find($id);

        if (!$record) {
            return false;
        }

        $updated = $record->update($data);
        $this->clearCache();

        return $updated;
    }

    /**
     * Delete prenatal record (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $record = $this->model->find($id);

        if (!$record) {
            return false;
        }

        $deleted = $record->delete();
        $this->clearCache();

        return $deleted;
    }

    /**
     * Get active prenatal records
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)
            ->where('status', '!=', 'completed')
            ->with('patient')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get active prenatal records paginated
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActivePaginated(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->where('is_active', true)
            ->where('status', '!=', 'completed')
            ->with('patient')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get prenatal records by status
     *
     * @param string $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->where('is_active', true)
            ->with('patient')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get prenatal records by patient ID
     *
     * @param int $patientId
     * @return Collection
     */
    public function getByPatient(int $patientId): Collection
    {
        return $this->model->where('patient_id', $patientId)
            ->with('prenatalCheckups')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get active prenatal record for patient
     *
     * @param int $patientId
     * @return PrenatalRecord|null
     */
    public function getActiveByPatient(int $patientId): ?PrenatalRecord
    {
        return $this->model->where('patient_id', $patientId)
            ->where('is_active', true)
            ->where('status', '!=', 'completed')
            ->with('prenatalCheckups')
            ->latest()
            ->first();
    }

    /**
     * Get high-risk prenatal records
     *
     * @return Collection
     */
    public function getHighRisk(): Collection
    {
        return $this->model->where('status', 'high-risk')
            ->where('is_active', true)
            ->with('patient')
            ->orderBy('expected_due_date', 'asc')
            ->get();
    }

    /**
     * Get overdue pregnancies
     *
     * @return Collection
     */
    public function getOverdue(): Collection
    {
        return $this->model->where('expected_due_date', '<', now())
            ->where('is_active', true)
            ->where('status', '!=', 'completed')
            ->with('patient')
            ->orderBy('expected_due_date', 'asc')
            ->get();
    }

    /**
     * Complete pregnancy (set status to completed)
     *
     * @param int $id
     * @return bool
     */
    public function completePregnancy(int $id): bool
    {
        $record = $this->model->find($id);

        if (!$record) {
            return false;
        }

        $updated = $record->update([
            'status' => 'completed',
            'is_active' => false,
        ]);

        $this->clearCache();

        return $updated;
    }

    /**
     * Update gestational age for all active records
     *
     * @return int Number of records updated
     */
    public function updateGestationalAges(): int
    {
        $records = $this->model->where('is_active', true)
            ->where('status', '!=', 'completed')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            $record->updateGestationalAge();
            $count++;
        }

        return $count;
    }

    /**
     * Search prenatal records
     *
     * @param string $term
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $term, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->where(function($query) use ($term) {
            $query->where('formatted_prenatal_id', 'LIKE', "%{$term}%")
                  ->orWhereHas('patient', function($q) use ($term) {
                      $q->where('first_name', 'LIKE', "%{$term}%")
                        ->orWhere('last_name', 'LIKE', "%{$term}%")
                        ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%");
                  });
        })
        ->with('patient')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
    }

    /**
     * Clear prenatal record related caches
     *
     * @return void
     */
    protected function clearCache(): void
    {
        Cache::forget('active_pregnancies_count');
        Cache::forget('dashboard_stats');
    }
}
