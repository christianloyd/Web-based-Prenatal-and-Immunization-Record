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
        // SMS is handled by PrenatalCheckupService::sendCheckupReminder()
        // This ensures we use the "Prenatal Checkup Reminder" type instead of "General" type
        // Only send in-app notifications here, not SMS

        // Patient will receive a reminder SMS 1 day before next visit via scheduled task (8AM/2PM)
    }

    /**
     * Handle the PrenatalCheckup "updated" event.
     */
    public function updated(PrenatalCheckup $prenatalCheckup): void
    {
        // SMS updates are handled by PrenatalCheckupService
        // Using "Prenatal Checkup Reminder" type for consistency
        // No SMS sent from Observer to avoid duplicates
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