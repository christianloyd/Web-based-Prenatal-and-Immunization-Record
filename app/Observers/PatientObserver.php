<?php

namespace App\Observers;

use App\Models\Patient;
use App\Services\NotificationService;

class PatientObserver
{
    /**
     * Handle the Patient "created" event.
     */
    public function created(Patient $patient): void
    {
        // Send notification when a new patient is registered
        NotificationService::sendNewPatientNotification($patient);
    }

    /**
     * Handle the Patient "updated" event.
     */
    public function updated(Patient $patient): void
    {
        // Optionally send notifications for important patient updates
        // For now, we'll skip this to avoid too many notifications
    }

    /**
     * Handle the Patient "deleted" event.
     */
    public function deleted(Patient $patient): void
    {
        //
    }

    /**
     * Handle the Patient "restored" event.
     */
    public function restored(Patient $patient): void
    {
        //
    }

    /**
     * Handle the Patient "force deleted" event.
     */
    public function forceDeleted(Patient $patient): void
    {
        //
    }
}