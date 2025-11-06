<?php

namespace App\Rules;

/**
 * Centralized Validation Rules
 *
 * Single source of truth for common validation rules used across the application
 */
class ValidationRules
{
    /**
     * Phone number validation (Philippine format)
     *
     * @return string
     */
    public static function phoneNumber(): string
    {
        return 'regex:/^(\+63|0)?9\d{9}$/';
    }

    /**
     * Name validation (letters, spaces, dots, hyphens, apostrophes)
     *
     * @return string
     */
    public static function name(): string
    {
        return 'regex:/^[a-zA-Z\s\.\-\']+$/';
    }

    /**
     * Age range validation
     *
     * @param int $min
     * @param int $max
     * @return string
     */
    public static function ageRange(int $min = 18, int $max = 100): string
    {
        return "integer|min:{$min}|max:{$max}";
    }

    /**
     * Patient age validation (15-50 for patients)
     *
     * @return string
     */
    public static function patientAge(): string
    {
        return 'integer|min:15|max:50';
    }

    /**
     * Blood pressure validation
     *
     * @return array
     */
    public static function bloodPressure(): array
    {
        return [
            'systolic' => 'required|integer|min:80|max:200',
            'diastolic' => 'required|integer|min:50|max:130',
        ];
    }

    /**
     * Weight validation (in kg)
     *
     * @return string
     */
    public static function weight(): string
    {
        return 'numeric|min:30|max:200';
    }

    /**
     * Date validation (not in future)
     *
     * @return string
     */
    public static function pastDate(): string
    {
        return 'date|before_or_equal:today';
    }

    /**
     * Birthdate validation
     *
     * @return string
     */
    public static function birthdate(): string
    {
        return 'required|date|before_or_equal:today|after:1900-01-01';
    }

    /**
     * Password validation rules (strong password)
     *
     * @return array
     */
    public static function password(): array
    {
        return [
            'required',
            'string',
            'min:8',
            'regex:/[a-z]/',      // At least one lowercase
            'regex:/[A-Z]/',      // At least one uppercase
            'regex:/[0-9]/',      // At least one number
            'regex:/[@$!%*#?&]/', // At least one special character
        ];
    }

    /**
     * Optional password validation (for updates)
     *
     * @return array
     */
    public static function passwordOptional(): array
    {
        return [
            'nullable',
            'string',
            'min:8',
            'regex:/[a-z]/',
            'regex:/[A-Z]/',
            'regex:/[0-9]/',
            'regex:/[@$!%*#?&]/',
        ];
    }

    /**
     * Username validation
     *
     * @param bool $unique
     * @param int|null $ignoreId
     * @return array|string
     */
    public static function username(bool $unique = true, ?int $ignoreId = null)
    {
        $rules = ['required', 'string', 'min:3', 'max:50', 'alpha_dash'];

        if ($unique) {
            $uniqueRule = 'unique:users,username';
            if ($ignoreId) {
                $uniqueRule .= ",{$ignoreId}";
            }
            $rules[] = $uniqueRule;
        }

        return $rules;
    }

    /**
     * Email validation
     *
     * @param bool $unique
     * @param int|null $ignoreId
     * @return array
     */
    public static function email(bool $unique = true, ?int $ignoreId = null): array
    {
        $rules = ['nullable', 'email', 'max:255'];

        if ($unique) {
            $uniqueRule = 'unique:users,email';
            if ($ignoreId) {
                $uniqueRule .= ",{$ignoreId}";
            }
            $rules[] = $uniqueRule;
        }

        return $rules;
    }

    /**
     * Get validation messages for common fields
     *
     * @return array
     */
    public static function messages(): array
    {
        return [
            'regex' => 'The :attribute format is invalid.',
            'phone_number.regex' => 'Phone number must be a valid Philippine mobile number (e.g., 09xxxxxxxxx or +639xxxxxxxxx).',
            'name.regex' => 'Name can only contain letters, spaces, dots, hyphens, and apostrophes.',
            'password.regex' => 'Password must contain at least one lowercase letter, one uppercase letter, one number, and one special character (@$!%*#?&).',
            'birthdate.before_or_equal' => 'Birthdate cannot be in the future.',
            'birthdate.after' => 'Birthdate must be after 1900.',
            'age.min' => 'Age must be at least :min years old.',
            'age.max' => 'Age cannot exceed :max years.',
            'weight.min' => 'Weight must be at least :min kg.',
            'weight.max' => 'Weight cannot exceed :max kg.',
        ];
    }

    /**
     * Get all common validation rules as array
     *
     * @return array
     */
    public static function commonRules(): array
    {
        return [
            'phone_number' => self::phoneNumber(),
            'name' => self::name(),
            'age' => self::ageRange(),
            'patient_age' => self::patientAge(),
            'weight' => self::weight(),
            'birthdate' => self::birthdate(),
            'past_date' => self::pastDate(),
        ];
    }
}
