<?php

namespace App\Utils;

class PhoneNumberFormatter
{
    /**
     * Format phone number to Philippine format (+63XXXXXXXXXX)
     *
     * @param string $phone
     * @return string
     */
    public static function format(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        // Convert to +63 format
        if (substr($digits, 0, 2) === '63') {
            return '+' . $digits;
        } elseif (substr($digits, 0, 1) === '0') {
            return '+63' . substr($digits, 1);
        } elseif (strlen($digits) === 10) {
            return '+63' . $digits;
        }

        // Return original if can't format
        return $phone;
    }

    /**
     * Validate Philippine phone number format
     *
     * @param string $phone
     * @return bool
     */
    public static function isValid(?string $phone): bool
    {
        if (empty($phone)) {
            return false;
        }

        // Check if matches Philippine phone pattern
        return (bool) preg_match('/^(\+63|0)[0-9]{10}$/', $phone);
    }

    /**
     * Format to display format (0XXX XXX XXXX)
     *
     * @param string $phone
     * @return string
     */
    public static function toDisplay(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $formatted = self::format($phone);
        $digits = preg_replace('/\D/', '', $formatted);

        if (strlen($digits) === 12 && substr($digits, 0, 2) === '63') {
            // Remove country code and format as 0XXX XXX XXXX
            $local = '0' . substr($digits, 2);
            return substr($local, 0, 4) . ' ' . substr($local, 4, 3) . ' ' . substr($local, 7);
        }

        return $phone;
    }
}
