<?php

namespace App\Repositories;

use App\Models\ChildImmunization;
use App\Repositories\Contracts\ChildImmunizationRepositoryInterface;
use Illuminate\Support\Collection;

class ChildImmunizationRepository implements ChildImmunizationRepositoryInterface
{
    protected $model;

    public function __construct(ChildImmunization $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with(['childRecord', 'vaccine'])->get();
    }

    public function find(int $id): ?ChildImmunization
    {
        return $this->model->find($id);
    }

    public function create(array $data): ChildImmunization
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $childImmunization = $this->find($id);
        return $childImmunization ? $childImmunization->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $childImmunization = $this->find($id);
        return $childImmunization ? $childImmunization->delete() : false;
    }

    public function getByChildRecord(int $childRecordId): Collection
    {
        return $this->model->where('child_record_id', $childRecordId)
            ->with('vaccine')
            ->orderBy('administered_date', 'desc')
            ->get();
    }

    public function getByVaccine(int $vaccineId): Collection
    {
        return $this->model->where('vaccine_id', $vaccineId)
            ->with('childRecord')
            ->orderBy('administered_date', 'desc')
            ->get();
    }
}
