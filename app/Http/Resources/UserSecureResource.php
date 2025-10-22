<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserSecureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * 
     * Only expose non-sensitive user data for API responses
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'status_text' => $this->status_text,
            'role_icon' => $this->role_icon,
            'created_at' => $this->created_at->format('M d, Y'),
            // Exclude sensitive data:
            // - username (login credentials)
            // - contact_number (personal info)
            // - address (personal info) 
            // - age (personal info)
            // - gender (personal info)
        ];
    }
}