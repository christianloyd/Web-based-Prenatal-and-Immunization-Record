<?php

namespace App\Services;

use App\Models\Appointment;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use App\Repositories\Contracts\PatientRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentService
{
    protected $appointmentRepository;
    protected $patientRepository;
    protected $notificationService;

    public function __construct(
        AppointmentRepositoryInterface $appointmentRepository,
        PatientRepositoryInterface $patientRepository,
        NotificationService $notificationService
    ) {
        $this->appointmentRepository = $appointmentRepository;
        $this->patientRepository = $patientRepository;
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new appointment
     *
     * @param array $data
     * @return Appointment
     */
    public function createAppointment(array $data): Appointment
    {
        return DB::transaction(function () use ($data) {
            // Verify patient exists
            $patient = $this->patientRepository->find($data['patient_id']);

            if (!$patient) {
                throw new \Exception('Patient not found');
            }

            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = 'scheduled';
            }

            $appointment = $this->appointmentRepository->create($data);

            Log::info('Appointment created', [
                'appointment_id' => $appointment->id,
                'patient_id' => $patient->id,
                'appointment_date' => $data['appointment_date'],
                'created_by' => Auth::id(),
            ]);

            // Send notification to patient (if SMS service is configured)
            // $this->notificationService->sendAppointmentNotification($appointment);

            return $appointment;
        });
    }

    /**
     * Update appointment
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateAppointment(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $result = $this->appointmentRepository->update($id, $data);

            if ($result) {
                Log::info('Appointment updated', [
                    'appointment_id' => $id,
                    'updated_by' => Auth::id(),
                ]);
            }

            return $result;
        });
    }

    /**
     * Cancel appointment
     *
     * @param int $id
     * @param string $reason
     * @return bool
     */
    public function cancelAppointment(int $id, string $reason): bool
    {
        return DB::transaction(function () use ($id, $reason) {
            $result = $this->appointmentRepository->cancel($id, $reason);

            if ($result) {
                Log::info('Appointment cancelled', [
                    'appointment_id' => $id,
                    'reason' => $reason,
                    'cancelled_by' => Auth::id(),
                ]);

                // Notify patient about cancellation
                // $appointment = $this->appointmentRepository->find($id);
                // $this->notificationService->sendAppointmentCancellationNotification($appointment);
            }

            return $result;
        });
    }

    /**
     * Reschedule appointment
     *
     * @param int $id
     * @param string $newDate
     * @param string $newTime
     * @return bool
     */
    public function rescheduleAppointment(int $id, string $newDate, string $newTime): bool
    {
        return DB::transaction(function () use ($id, $newDate, $newTime) {
            $result = $this->appointmentRepository->reschedule($id, $newDate, $newTime);

            if ($result) {
                Log::info('Appointment rescheduled', [
                    'appointment_id' => $id,
                    'new_date' => $newDate,
                    'new_time' => $newTime,
                    'rescheduled_by' => Auth::id(),
                ]);

                // Notify patient about reschedule
                // $appointment = $this->appointmentRepository->find($id);
                // $this->notificationService->sendAppointmentRescheduleNotification($appointment);
            }

            return $result;
        });
    }

    /**
     * Complete appointment
     *
     * @param int $id
     * @return bool
     */
    public function completeAppointment(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $result = $this->appointmentRepository->complete($id);

            if ($result) {
                Log::info('Appointment completed', [
                    'appointment_id' => $id,
                    'completed_by' => Auth::id(),
                ]);
            }

            return $result;
        });
    }

    /**
     * Get today's appointments
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTodayAppointments()
    {
        return $this->appointmentRepository->getToday();
    }

    /**
     * Get upcoming appointments
     *
     * @param int $days
     * @return \Illuminate\Support\Collection
     */
    public function getUpcomingAppointments(int $days = 7)
    {
        return $this->appointmentRepository->getUpcoming($days);
    }

    /**
     * Get appointments by patient
     *
     * @param int $patientId
     * @return \Illuminate\Support\Collection
     */
    public function getPatientAppointments(int $patientId)
    {
        return $this->appointmentRepository->getByPatient($patientId);
    }

    /**
     * Get appointment statistics
     *
     * @return array
     */
    public function getAppointmentStatistics(): array
    {
        return [
            'today' => $this->appointmentRepository->getToday()->count(),
            'upcoming_week' => $this->appointmentRepository->getUpcoming(7)->count(),
            'upcoming_month' => $this->appointmentRepository->getUpcoming(30)->count(),
            'scheduled' => $this->appointmentRepository->getByStatus('scheduled')->count(),
            'completed' => $this->appointmentRepository->getByStatus('completed')->count(),
            'cancelled' => $this->appointmentRepository->getByStatus('cancelled')->count(),
            'rescheduled' => $this->appointmentRepository->getByStatus('rescheduled')->count(),
        ];
    }

    /**
     * Check for appointment conflicts
     *
     * @param string $date
     * @param string $time
     * @param int|null $excludeAppointmentId
     * @return bool
     */
    public function hasConflict(string $date, string $time, ?int $excludeAppointmentId = null): bool
    {
        $appointments = $this->appointmentRepository->all()
            ->where('appointment_date', $date)
            ->where('appointment_time', $time)
            ->whereIn('status', ['scheduled', 'confirmed']);

        if ($excludeAppointmentId) {
            $appointments = $appointments->where('id', '!=', $excludeAppointmentId);
        }

        return $appointments->count() > 0;
    }

    /**
     * Get available time slots for a date
     *
     * @param string $date
     * @return array
     */
    public function getAvailableTimeSlots(string $date): array
    {
        // Define working hours (8 AM to 5 PM)
        $workingHours = [
            '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
            '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
        ];

        $bookedSlots = $this->appointmentRepository->all()
            ->where('appointment_date', $date)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->pluck('appointment_time')
            ->toArray();

        return array_values(array_diff($workingHours, $bookedSlots));
    }
}
