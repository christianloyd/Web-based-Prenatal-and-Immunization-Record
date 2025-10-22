<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBloodPressure implements ValidationRule
{
    protected $type; // 'systolic' or 'diastolic'
    protected $otherValue;

    public function __construct($type, $otherValue = null)
    {
        $this->type = $type;
        $this->otherValue = $otherValue;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return; // Allow null values
        }

        if ($this->type === 'systolic') {
            // Systolic validation
            if ($value < 80) {
                $fail('Systolic blood pressure is dangerously low (<80). Please verify reading.');
            } elseif ($value > 200) {
                $fail('Systolic blood pressure is dangerously high (>200). Please verify reading.');
            } elseif ($this->otherValue !== null && $value <= $this->otherValue) {
                $fail('Systolic blood pressure must be higher than diastolic.');
            }
        } else {
            // Diastolic validation
            if ($value < 50) {
                $fail('Diastolic blood pressure is dangerously low (<50). Please verify reading.');
            } elseif ($value > 130) {
                $fail('Diastolic blood pressure is dangerously high (>130). Please verify reading.');
            }
        }
    }

    /**
     * Get warning level based on blood pressure reading
     *
     * @param int $systolic
     * @param int $diastolic
     * @return array ['level' => 'normal|warning|danger', 'message' => 'warning text']
     */
    public static function getWarningLevel($systolic, $diastolic)
    {
        if (!$systolic || !$diastolic) {
            return null;
        }

        // Severe hypertension (Emergency)
        if ($systolic >= 180 || $diastolic >= 120) {
            return [
                'level' => 'danger',
                'message' => '🚨 HYPERTENSIVE EMERGENCY: BP ≥180/120. Immediate hospital referral required!'
            ];
        }

        // Stage 2 Hypertension (High Risk)
        if ($systolic >= 160 || $diastolic >= 110) {
            return [
                'level' => 'danger',
                'message' => '⚠️ SEVERE HYPERTENSION: BP ≥160/110. Urgent medical attention needed. Possible severe pre-eclampsia.'
            ];
        }

        // Stage 1 Hypertension (Moderate Risk)
        if ($systolic >= 140 || $diastolic >= 90) {
            return [
                'level' => 'warning',
                'message' => '⚠️ HIGH BLOOD PRESSURE: BP ≥140/90. Possible pre-eclampsia. Close monitoring required.'
            ];
        }

        // Elevated BP (Watch closely)
        if ($systolic >= 130 || $diastolic >= 85) {
            return [
                'level' => 'info',
                'message' => 'ℹ️ ELEVATED BLOOD PRESSURE: Monitor closely at next visit.'
            ];
        }

        // Hypotension (Low BP)
        if ($systolic < 90 || $diastolic < 60) {
            return [
                'level' => 'warning',
                'message' => '⚠️ LOW BLOOD PRESSURE: BP <90/60. Check for dehydration, anemia, or other causes.'
            ];
        }

        // Normal range
        return [
            'level' => 'success',
            'message' => '✅ Normal blood pressure range for pregnancy.'
        ];
    }
}
