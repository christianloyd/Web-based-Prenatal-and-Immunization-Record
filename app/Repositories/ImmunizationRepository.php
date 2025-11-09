<?php

namespace App\Repositories;

use App\Models\Immunization;
use App\Repositories\Contracts\ImmunizationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ImmunizationRepository implements ImmunizationRepositoryInterface
{
    /**
     * @var Immunization
     */
    protected $model;

    public function __construct(Immunization $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with(['childRecord', 'vaccine'])->get();
    }

    public function find(int $id): ?Immunization
    {
        return $this->model->find($id);
    }

    public function findWithRelations(int $id, array $relations = []): ?Immunization
    {
        return $this->model->with($relations)->find($id);
    }

    public function create(array $data): Immunization
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $immunization = $this->find($id);

        if (!$immunization) {
            return false;
        }

        return $immunization->update($data);
    }

    public function delete(int $id): bool
    {
        $immunization = $this->find($id);

        if (!$immunization) {
            return false;
        }

        return $immunization->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['childRecord', 'vaccine'])
            ->orderBy('schedule_date', 'desc')
            ->paginate($perPage);
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->with(['childRecord', 'vaccine'])
            ->orderBy('schedule_date')
            ->get();
    }

    public function getUpcoming(int $days = 7): Collection
    {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays($days);

        return $this->model->where('status', 'Upcoming')
            ->whereBetween('schedule_date', [$startDate, $endDate])
            ->with(['childRecord', 'vaccine'])
            ->orderBy('schedule_date')
            ->get();
    }

    public function getOverdue(): Collection
    {
        return $this->model->where('status', 'Upcoming')
            ->where('schedule_date', '<', Carbon::now())
            ->with(['childRecord', 'vaccine'])
            ->orderBy('schedule_date')
            ->get();
    }

    public function getByChildRecord(int $childRecordId): Collection
    {
        return $this->model->where('child_record_id', $childRecordId)
            ->with('vaccine')
            ->orderBy('schedule_date')
            ->get();
    }

    public function markAsDone(int $id, array $data = []): bool
    {
        $immunization = $this->find($id);

        if (!$immunization) {
            return false;
        }

        $updateData = array_merge([
            'status' => 'Done',
            'administered_date' => $data['administered_date'] ?? Carbon::now(),
        ], $data);

        return $immunization->update($updateData);
    }

    public function markAsMissed(int $id, string $reason): bool
    {
        $immunization = $this->find($id);

        if (!$immunization) {
            return false;
        }

        return $immunization->update([
            'status' => 'Missed',
            'notes' => $reason,
        ]);
    }

    public function reschedule(int $id, string $newDate, ?string $newTime = null): bool
    {
        $immunization = $this->find($id);

        if (!$immunization) {
            return false;
        }

        $updateData = [
            'schedule_date' => $newDate,
            'status' => 'Upcoming',
        ];

        if ($newTime) {
            $updateData['schedule_time'] = $newTime;
        }

        return $immunization->update($updateData);
    }

    public function search(string $term): Collection
    {
        return $this->model->whereHas('childRecord', function ($query) use ($term) {
                $query->where('first_name', 'LIKE', "%{$term}%")
                    ->orWhere('last_name', 'LIKE', "%{$term}%");
            })
            ->orWhereHas('vaccine', function ($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%");
            })
            ->with(['childRecord', 'vaccine'])
            ->get();
    }
}
