<?php

namespace App\Utils;

use Carbon\Carbon;

class DateCalculator
{
    /**
     * Calculate Expected Due Date from Last Menstrual Period
     * Uses Naegele's Rule: LMP + 280 days (40 weeks)
     *
     * @param string $lmp Last Menstrual Period date
     * @return Carbon
     */
    public static function calculateEDD(string $lmp): Carbon
    {
        return Carbon::parse($lmp)->addDays(280);
    }

    /**
     * Calculate gestational age in weeks from LMP
     *
     * @param string $lmp Last Menstrual Period date
     * @param string|null $referenceDate Date to calculate from (defaults to today)
     * @return int Gestational age in whole weeks
     */
    public static function calculateGestationalWeeks(string $lmp, ?string $referenceDate = null): int
    {
        $lmpDate = Carbon::parse($lmp);
        $reference = $referenceDate ? Carbon::parse($referenceDate) : Carbon::now();

        $totalDays = $lmpDate->diffInDays($reference);

        return intval($totalDays / 7);
    }

    /**
     * Calculate gestational age as formatted string (e.g., "24 weeks" or "1 week")
     *
     * @param string $lmp Last Menstrual Period date
     * @param string|null $referenceDate Date to calculate from (defaults to today)
     * @return string Formatted gestational age
     */
    public static function formatGestationalAge(string $lmp, ?string $referenceDate = null): string
    {
        $weeks = self::calculateGestationalWeeks($lmp, $referenceDate);

        return $weeks === 1 ? "1 week" : "{$weeks} weeks";
    }

    /**
     * Calculate trimester from gestational weeks
     *
     * @param int $weeks Gestational age in weeks
     * @return int Trimester (1, 2, or 3)
     */
    public static function calculateTrimester(int $weeks): int
    {
        if ($weeks <= 12) {
            return 1;
        } elseif ($weeks <= 27) {
            return 2;
        }

        return 3;
    }

    /**
     * Calculate trimester from LMP
     *
     * @param string $lmp Last Menstrual Period date
     * @return int Trimester (1, 2, or 3)
     */
    public static function calculateTrimesterFromLMP(string $lmp): int
    {
        $weeks = self::calculateGestationalWeeks($lmp);
        return self::calculateTrimester($weeks);
    }

    /**
     * Check if pregnancy is high risk based on maternal age
     *
     * @param int $age Maternal age
     * @return bool True if high risk (< 18 or > 35)
     */
    public static function isHighRiskAge(int $age): bool
    {
        return $age < 18 || $age > 35;
    }

    /**
     * Calculate age from date of birth
     *
     * @param string $dateOfBirth
     * @return int Age in years
     */
    public static function calculateAge(string $dateOfBirth): int
    {
        return Carbon::parse($dateOfBirth)->age;
    }

    /**
     * Calculate age in months (useful for child immunizations)
     *
     * @param string $dateOfBirth
     * @return int Age in months
     */
    public static function calculateAgeInMonths(string $dateOfBirth): int
    {
        return Carbon::parse($dateOfBirth)->diffInMonths(Carbon::now());
    }

    /**
     * Check if date is within range
     *
     * @param string $date Date to check
     * @param string $startDate Start of range
     * @param string $endDate End of range
     * @return bool
     */
    public static function isWithinRange(string $date, string $startDate, string $endDate): bool
    {
        $checkDate = Carbon::parse($date);
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        return $checkDate->between($start, $end);
    }

    /**
     * Check if date is overdue (past today)
     *
     * @param string $date
     * @return bool
     */
    public static function isOverdue(string $date): bool
    {
        return Carbon::parse($date)->isPast();
    }

    /**
     * Get days until date
     *
     * @param string $date
     * @return int Negative if past
     */
    public static function daysUntil(string $date): int
    {
        return Carbon::now()->diffInDays(Carbon::parse($date), false);
    }

    /**
     * Format date for display
     *
     * @param string $date
     * @param string $format Default: 'F d, Y' (e.g., January 15, 2024)
     * @return string
     */
    public static function formatForDisplay(string $date, string $format = 'F d, Y'): string
    {
        return Carbon::parse($date)->format($format);
    }
}
