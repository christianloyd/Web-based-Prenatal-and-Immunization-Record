<?php

namespace App\Enums;

/**
 * Immunization Status Enum
 *
 * Defines the possible statuses for immunization records
 */
enum ImmunizationStatus: string
{
    case UPCOMING = 'Upcoming';
    case DONE = 'Done';
    case MISSED = 'Missed';

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
            self::UPCOMING => 'Upcoming',
            self::DONE => 'Completed',
            self::MISSED => 'Missed',
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
            self::UPCOMING => 'bg-blue-100 text-blue-800',
            self::DONE => 'bg-green-100 text-green-800',
            self::MISSED => 'bg-red-100 text-red-800',
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
            self::UPCOMING => 'fa-clock',
            self::DONE => 'fa-check-circle',
            self::MISSED => 'fa-times-circle',
        };
    }

    /**
     * Check if status is upcoming
     *
     * @return bool
     */
    public function isUpcoming(): bool
    {
        return $this === self::UPCOMING;
    }

    /**
     * Check if status is done
     *
     * @return bool
     */
    public function isDone(): bool
    {
        return $this === self::DONE;
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
}
