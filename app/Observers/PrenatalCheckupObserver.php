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
        // Send SMS for NEXT VISIT DATE only (not current checkup date)
        // Current checkup date is today (already happening), no need for SMS
        // Next visit date is the future appointment that needs reminder
        if (!empty($prenatalCheckup->next_visit_date)) {
            NotificationService::sendAppointmentConfirmation($prenatalCheckup);
        }
        // Patient will ALSO receive a reminder SMS 1 day before next visit via scheduled task (8AM/2PM)
    }

    /**
     * Handle the PrenatalCheckup "updated" event.
     */
    public function updated(PrenatalCheckup $prenatalCheckup): void
    {
        // Send confirmation SMS if the checkup date was changed
        if ($prenatalCheckup->wasChanged('checkup_date')) {
            NotificationService::sendAppointmentConfirmation($prenatalCheckup);
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