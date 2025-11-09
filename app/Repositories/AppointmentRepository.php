<?php

namespace App\Repositories;

use App\Models\Appointment;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AppointmentRepository implements AppointmentRepositoryInterface
{
    protected $model;

    public function __construct(Appointment $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with('patient')->get();
    }

    public function find(int $id): ?Appointment
    {
        return $this->model->find($id);
    }

    public function findWithRelations(int $id, array $relations = []): ?Appointment
    {
        return $this->model->with($relations)->find($id);
    }

    public function create(array $data): Appointment
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $appointment = $this->find($id);
        return $appointment ? $appointment->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $appointment = $this->find($id);
        return $appointment ? $appointment->delete() : false;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('patient')
            ->orderBy('appointment_date', 'desc')
            ->paginate($perPage);
    }

    public function getByPatient(int $patientId): Collection
    {
        return $this->model->where('patient_id', $patientId)
            ->orderBy('appointment_date', 'desc')
            ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->with('patient')
            ->orderBy('appointment_date')
            ->get();
    }

    public function getUpcoming(int $days = 7): Collection
    {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays($days);

        return $this->model->whereBetween('appointment_date', [$startDate, $endDate])
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->with('patient')
            ->orderBy('appointment_date')
            ->get();
    }

    public function getToday(): Collection
    {
        return $this->model->whereDate('appointment_date', Carbon::today())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->with('patient')
            ->orderBy('appointment_time')
            ->get();
    }

    public function cancel(int $id, string $reason): bool
    {
        $appointment = $this->find($id);

        if (!$appointment) {
            return false;
        }

        return $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
        ]);
    }

    public function reschedule(int $id, string $newDate, string $newTime): bool
    {
        $appointment = $this->find($id);

        if (!$appointment) {
            return false;
        }

        return $appointment->update([
            'appointment_date' => $newDate,
            'appointment_time' => $newTime,
            'status' => 'rescheduled',
        ]);
    }

    public function complete(int $id): bool
    {
        $appointment = $this->find($id);

        if (!$appointment) {
            return false;
        }

        return $appointment->update(['status' => 'completed']);
    }
}
