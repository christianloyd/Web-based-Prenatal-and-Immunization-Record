<?php

namespace App\Observers;

use App\Models\PrenatalCheckup;
use App\Services\NotificationService;

class PrenatalCheckupObserver
{
    /**
     * Handle the PrenatalCheckup "created" event.
     */
    public function created(PrenatalCheckup $prenatalCheckup): void
    {
        // Send notification when a new prenatal checkup is scheduled
        NotificationService::sendAppointmentReminder($prenatalCheckup);
    }

    /**
     * Handle the PrenatalCheckup "updated" event.
     */
    public function updated(PrenatalCheckup $prenatalCheckup): void
    {
        // Send notification if the checkup date was changed
        if ($prenatalCheckup->wasChanged('checkup_date')) {
            NotificationService::sendAppointmentReminder($prenatalCheckup);
        }
    }

    /**
     * Handle the PrenatalCheckup "deleted" event.
     */
    public function deleted(PrenatalCheckup $prenatalCheckup): void
    {
        //
    }

    /**
     * Handle the PrenatalCheckup "restored" event.
     */
    public function restored(PrenatalCheckup $prenatalCheckup): void
    {
        //
    }

    /**
     * Handle the PrenatalCheckup "force deleted" event.
     */
    public function forceDeleted(PrenatalCheckup $prenatalCheckup): void
    {
        //
    }
}