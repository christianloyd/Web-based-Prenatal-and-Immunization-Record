<?php

namespace App\Utils;

class ValidationHelper
{
    /**
     * Common validation rules for Philippine phone numbers
     *
     * @return array
     */
    public static function phoneNumberRules(): array
    {
        return [
            'required',
            'string',
            'max:13',
            'regex:/^(\+63|0)[0-9]{10}$/'
        ];
    }

    /**
     * Optional phone number validation rules
     *
     * @return array
     */
    public static function optionalPhoneNumberRules(): array
    {
        return [
            'nullable',
            'string',
            'max:13',
            'regex:/^(\+63|0)[0-9]{10}$/'
        ];
    }

    /**
     * Name validation rules (letters, spaces, dots, hyphens, apostrophes)
     *
     * @param int $min Minimum length
     * @param int $max Maximum length
     * @return array
     */
    public static function nameRules(int $min = 2, int $max = 50): array
    {
        return [
            'required',
            'string',
            "min:{$min}",
            "max:{$max}",
            'regex:/^[a-zA-Z\s\.\-\']+$/'
        ];
    }

    /**
     * Age validation rules for maternal age
     *
     * @return array
     */
    public static function maternalAgeRules(): array
    {
        return [
            'required',
            'integer',
            'min:15',
            'max:50'
        ];
    }

    /**
     * Date validation rules (not in future)
     *
     * @return array
     */
    public static function pastDateRules(): array
    {
        return [
            'required',
            'date',
            'before_or_equal:today'
        ];
    }

    /**
     * Date validation rules (future dates)
     *
     * @return array
     */
    public static function futureDateRules(): array
    {
        return [
            'required',
            'date',
            'after_or_equal:today'
        ];
    }

    /**
     * Blood pressure validation pattern
     *
     * @return array
     */
    public static function bloodPressureRules(): array
    {
        return [
            'required',
            'string',
            'regex:/^\d{2,3}\/\d{2,3}$/' // Matches 120/80 format
        ];
    }

    /**
     * Validate weight (in kg)
     *
     * @return array
     */
    public static function weightRules(): array
    {
        return [
            'required',
            'numeric',
            'min:1',
            'max:300'
        ];
    }

    /**
     * Validate height (in cm)
     *
     * @return array
     */
    public static function heightRules(): array
    {
        return [
            'required',
            'numeric',
            'min:50',
            'max:250'
        ];
    }

    /**
     * Custom error messages for phone numbers
     *
     * @param string $field Field name for error message
     * @return array
     */
    public static function phoneNumberMessages(string $field = 'contact'): array
    {
        return [
            "{$field}.required" => 'Phone number is required.',
            "{$field}.regex" => 'Please enter a valid Philippine phone number (e.g., +639123456789 or 09123456789).',
            "{$field}.max" => 'Phone number is too long.',
        ];
    }

    /**
     * Custom error messages for names
     *
     * @param string $field Field name for error message
     * @return array
     */
    public static function nameMessages(string $field = 'name'): array
    {
        return [
            "{$field}.required" => ucfirst($field) . ' is required.',
            "{$field}.min" => ucfirst($field) . ' must be at least :min characters.',
            "{$field}.max" => ucfirst($field) . ' cannot exceed :max characters.',
            "{$field}.regex" => ucfirst($field) . ' should only contain letters, spaces, dots, hyphens, and apostrophes.',
        ];
    }
}
