<?php

namespace App\Observers;

use App\Models\Vaccine;
use App\Services\NotificationService;

class VaccineObserver
{
    /**
     * Handle the Vaccine "created" event.
     */
    public function created(Vaccine $vaccine): void
    {
        //
    }

    /**
     * Handle the Vaccine "updated" event.
     */
    public function updated(Vaccine $vaccine): void
    {
        // Check if stock quantity was updated and is now low
        if ($vaccine->wasChanged('stock_quantity')) {
            $threshold = $vaccine->minimum_threshold ?? 10;
            
            if ($vaccine->stock_quantity <= $threshold && $vaccine->stock_quantity > 0) {
                NotificationService::sendLowStockAlert($vaccine);
            }
        }
    }

    /**
     * Handle the Vaccine "deleted" event.
     */
    public function deleted(Vaccine $vaccine): void
    {
        //
    }

    /**
     * Handle the Vaccine "restored" event.
     */
    public function restored(Vaccine $vaccine): void
    {
        //
    }

    /**
     * Handle the Vaccine "force deleted" event.
     */
    public function forceDeleted(Vaccine $vaccine): void
    {
        //
    }
}