<?php

namespace App\Repositories\Contracts;

use App\Models\Appointment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AppointmentRepositoryInterface
{
    /**
     * Get all appointments
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Find appointment by ID
     *
     * @param int $id
     * @return Appointment|null
     */
    public function find(int $id): ?Appointment;

    /**
     * Find appointment with relationships
     *
     * @param int $id
     * @param array $relations
     * @return Appointment|null
     */
    public function findWithRelations(int $id, array $relations = []): ?Appointment;

    /**
     * Create new appointment
     *
     * @param array $data
     * @return Appointment
     */
    public function create(array $data): Appointment;

    /**
     * Update appointment
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete appointment
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get paginated appointments
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get appointments by patient
     *
     * @param int $patientId
     * @return Collection
     */
    public function getByPatient(int $patientId): Collection;

    /**
     * Get appointments by status
     *
     * @param string $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get upcoming appointments
     *
     * @param int $days
     * @return Collection
     */
    public function getUpcoming(int $days = 7): Collection;

    /**
     * Get today's appointments
     *
     * @return Collection
     */
    public function getToday(): Collection;

    /**
     * Cancel appointment
     *
     * @param int $id
     * @param string $reason
     * @return bool
     */
    public function cancel(int $id, string $reason): bool;

    /**
     * Reschedule appointment
     *
     * @param int $id
     * @param string $newDate
     * @param string $newTime
     * @return bool
     */
    public function reschedule(int $id, string $newDate, string $newTime): bool;

    /**
     * Complete appointment
     *
     * @param int $id
     * @return bool
     */
    public function complete(int $id): bool;
}
