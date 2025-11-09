<?php

namespace App\Repositories;

use App\Models\PrenatalVisit;
use App\Repositories\Contracts\PrenatalVisitRepositoryInterface;
use Illuminate\Support\Collection;

class PrenatalVisitRepository implements PrenatalVisitRepositoryInterface
{
    protected $model;

    public function __construct(PrenatalVisit $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with(['prenatalRecord', 'patient'])->get();
    }

    public function find(int $id): ?PrenatalVisit
    {
        return $this->model->find($id);
    }

    public function create(array $data): PrenatalVisit
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $visit = $this->find($id);
        return $visit ? $visit->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $visit = $this->find($id);
        return $visit ? $visit->delete() : false;
    }

    public function getByPrenatalRecord(int $prenatalRecordId): Collection
    {
        return $this->model->where('prenatal_record_id', $prenatalRecordId)
            ->orderBy('visit_date', 'desc')
            ->get();
    }

    public function getByPatient(int $patientId): Collection
    {
        return $this->model->where('patient_id', $patientId)
            ->with('prenatalRecord')
            ->orderBy('visit_date', 'desc')
            ->get();
    }
}
