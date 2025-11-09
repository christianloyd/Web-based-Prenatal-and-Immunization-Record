<?php

namespace App\Repositories;

use App\Models\PrenatalCheckup;
use App\Repositories\Contracts\PrenatalCheckupRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PrenatalCheckupRepository implements PrenatalCheckupRepositoryInterface
{
    /**
     * @var PrenatalCheckup
     */
    protected $model;

    public function __construct(PrenatalCheckup $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with(['patient', 'prenatalRecord'])->get();
    }

    public function find(int $id): ?PrenatalCheckup
    {
        return $this->model->find($id);
    }

    public function findWithRelations(int $id, array $relations = []): ?PrenatalCheckup
    {
        return $this->model->with($relations)->find($id);
    }

    public function create(array $data): PrenatalCheckup
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $checkup = $this->find($id);

        if (!$checkup) {
            return false;
        }

        return $checkup->update($data);
    }

    public function delete(int $id): bool
    {
        $checkup = $this->find($id);

        if (!$checkup) {
            return false;
        }

        return $checkup->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['patient', 'prenatalRecord'])
            ->orderBy('checkup_date', 'desc')
            ->paginate($perPage);
    }

    public function getByPatient(int $patientId): Collection
    {
        return $this->model->where('patient_id', $patientId)
            ->with('prenatalRecord')
            ->orderBy('checkup_date', 'desc')
            ->get();
    }

    public function getByPrenatalRecord(int $prenatalRecordId): Collection
    {
        return $this->model->where('prenatal_record_id', $prenatalRecordId)
            ->orderBy('checkup_date', 'desc')
            ->get();
    }

    public function getLatestForPatient(int $patientId): ?PrenatalCheckup
    {
        return $this->model->where('patient_id', $patientId)
            ->orderBy('checkup_date', 'desc')
            ->first();
    }

    public function getUpcoming(int $days = 7): Collection
    {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays($days);

        return $this->model->whereNotNull('next_visit_date')
            ->whereBetween('next_visit_date', [$startDate, $endDate])
            ->with(['patient', 'prenatalRecord'])
            ->orderBy('next_visit_date')
            ->get();
    }

    public function getOverdue(): Collection
    {
        return $this->model->whereNotNull('next_visit_date')
            ->where('next_visit_date', '<', Carbon::now())
            ->whereDoesntHave('patient.prenatalCheckups', function ($query) {
                $query->where('checkup_date', '>=', Carbon::now());
            })
            ->with(['patient', 'prenatalRecord'])
            ->orderBy('next_visit_date')
            ->get();
    }

    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->whereBetween('checkup_date', [$startDate, $endDate])
            ->with(['patient', 'prenatalRecord'])
            ->orderBy('checkup_date')
            ->get();
    }

    public function search(string $term): Collection
    {
        return $this->model->whereHas('patient', function ($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%");
            })
            ->with(['patient', 'prenatalRecord'])
            ->get();
    }
}
