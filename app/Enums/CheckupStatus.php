<?php

namespace App\Enums;

/**
 * Prenatal Checkup Status Enum
 *
 * Defines the possible statuses for prenatal checkup records
 */
enum CheckupStatus: string
{
    case SCHEDULED = 'Scheduled';
    case COMPLETED = 'Completed';
    case MISSED = 'Missed';
    case CANCELLED = 'Cancelled';

    /**
     * Get all status values as array
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all status names as array
     *
     * @return array
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Get status label for display
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::SCHEDULED => 'Scheduled',
            self::COMPLETED => 'Completed',
            self::MISSED => 'Missed',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Get status badge class for UI
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::SCHEDULED => 'bg-blue-100 text-blue-800',
            self::COMPLETED => 'bg-green-100 text-green-800',
            self::MISSED => 'bg-red-100 text-red-800',
            self::CANCELLED => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get status icon
     *
     * @return string
     */
    public function icon(): string
    {
        return match($this) {
            self::SCHEDULED => 'fa-calendar',
            self::COMPLETED => 'fa-check-circle',
            self::MISSED => 'fa-times-circle',
            self::CANCELLED => 'fa-ban',
        };
    }

    /**
     * Check if status is scheduled
     *
     * @return bool
     */
    public function isScheduled(): bool
    {
        return $this === self::SCHEDULED;
    }

    /**
     * Check if status is completed
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if status is missed
     *
     * @return bool
     */
    public function isMissed(): bool
    {
        return $this === self::MISSED;
    }

    /**
     * Check if status is cancelled
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }
}
