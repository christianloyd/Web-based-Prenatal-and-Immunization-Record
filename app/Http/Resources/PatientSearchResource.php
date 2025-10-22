<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * 
     * Only expose the minimum necessary data for patient search
     * Protects sensitive healthcare information from browser dev tools
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            // Show full names for healthcare workers (Option 1 - Better UX)
            'name' => $this->getFullName(),
            // Include individual fields for JavaScript compatibility
            'first_name' => $this->first_name ?? $this->getFirstName(),
            'last_name' => $this->last_name ?? $this->getLastName(),
            // Display name (same as name for consistency)
            'display_name' => $this->getFullName(),
            'formatted_patient_id' => $this->formatted_patient_id ?? 'P-' . str_pad($this->id, 3, '0', STR_PAD_LEFT),
            'age' => $this->age,
            // Keep contact numbers masked for privacy protection
            'contact' => $this->getMaskedContact(),
            // Legacy field for backward compatibility
            'patient_id' => $this->formatted_patient_id ?? 'PT-' . str_pad($this->id, 3, '0', STR_PAD_LEFT),
            // Search-friendly data with full names
            'search_text' => $this->getSearchText(),
        ];
    }


    /**
     * Get masked contact number for privacy protection
     */
    private function getMaskedContact()
    {
        if (!$this->contact) return 'N/A';
        
        $contact = $this->contact;
        // Remove any non-digit characters
        $digits = preg_replace('/\D/', '', $contact);
        $length = strlen($digits);
        
        if ($length >= 6) {
            // Show first 2 and last 2 digits: 09XX-XXX-XX21
            return substr($digits, 0, 2) . str_repeat('X', max(0, $length - 4)) . substr($digits, -2);
        }
        
        return str_repeat('X', $length);
    }

    /**
     * Get searchable text for client-side filtering with full names
     */
    private function getSearchText()
    {
        $searchParts = [];
        
        // Include full names and IDs for better search functionality
        $fullName = $this->getFullName();
        if ($fullName) {
            $searchParts[] = strtolower($fullName);
        }
        if ($this->formatted_patient_id) {
            $searchParts[] = strtolower($this->formatted_patient_id);
        }
        
        return implode(' ', $searchParts);
    }

    /**
     * Get full name for healthcare worker identification
     */
    private function getFullName()
    {
        // Return the complete name for better user experience
        return $this->name ?? ($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get first name (full first name, not initial)
     */
    private function getFirstName()
    {
        if ($this->first_name) {
            return $this->first_name;
        }
        
        if ($this->name) {
            $nameParts = explode(' ', trim($this->name));
            return $nameParts[0] ?? 'Unknown';
        }
        
        return 'Unknown';
    }

    /**
     * Get last name (full last name for identification)
     */
    private function getLastName()
    {
        if ($this->last_name) {
            return $this->last_name;
        }
        
        if ($this->name) {
            $nameParts = explode(' ', trim($this->name));
            if (count($nameParts) > 1) {
                return end($nameParts);
            }
        }
        
        return 'Patient';
    }
}
