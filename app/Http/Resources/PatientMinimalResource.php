<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientMinimalResource extends JsonResource
{
    /**
     * Transform the resource into minimal data for security.
     * 
     * HYBRID APPROACH: Only expose initials + ID for search
     * Full details retrieved after selection via separate secure call
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'initials' => $this->getInitials(),
            'patient_code' => $this->formatted_patient_id ?? 'PT-' . str_pad($this->id, 3, '0', STR_PAD_LEFT),
            'age_range' => $this->getAgeRange(),
        ];
    }

    /**
     * Get patient initials only (M.S.)
     */
    private function getInitials()
    {
        $name = $this->name ?? ($this->first_name . ' ' . $this->last_name);
        $parts = explode(' ', trim($name));
        $initials = '';
        
        foreach ($parts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper(substr($part, 0, 1)) . '.';
            }
        }
        
        return rtrim($initials, '.');
    }

    /**
     * Get age range instead of exact age (20-25, 26-30, etc.)
     */
    private function getAgeRange()
    {
        if (!$this->age) return 'N/A';
        
        $age = $this->age;
        $rangeStart = floor($age / 5) * 5;
        $rangeEnd = $rangeStart + 4;
        
        return $rangeStart . '-' . $rangeEnd;
    }
}