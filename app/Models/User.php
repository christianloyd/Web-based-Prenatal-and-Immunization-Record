<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\HasDatabaseNotifications;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasDatabaseNotifications;

    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'gender',
        'age',
        'contact_number',
        'address',
        'is_active',
    ];

    protected $hidden = [
        'password', 
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Validation rules
    public static function validationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'gender' => 'required|in:Male,Female',
            'age' => 'required|integer|min:18|max:100',
            'contact_number' => 'required|regex:/^9\d{9}$/|unique:users,contact_number',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:Midwife,BHW',
            'password' => 'required|string|min:8',
            'is_active' => 'sometimes|boolean', // Add validation for is_active
        ];

        if ($isUpdate) {
            $rules['password'] = 'nullable|string|min:8';
        } 

        return $rules;
    }
    
    // Add scope for active users only
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Add scope for inactive users only
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // Add scope for filtering by status
    public function scopeByStatus($query, $status)
    {
        if ($status === 'active') {
            return $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            return $query->where('is_active', false);
        }
        return $query;
    }

    // Check if user is active
    public function isActive()
    {
        return $this->is_active;
    }

    // Check if user is inactive
    public function isInactive()
    {
        return !$this->is_active;
    }

    // Get status badge class
    public function getStatusBadgeClassAttribute()
    {
        return $this->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    }

    // Get status text
    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    // Get status icon
    public function getStatusIconAttribute()
    {
        return $this->is_active ? 'fa-check-circle' : 'fa-times-circle';
    }

    // Override toArray to include status attributes
    public function toArray()
    {
        $array = parent::toArray();
        $array['formatted_contact_number'] = $this->formatted_contact_number;
        $array['role_badge_class'] = $this->role_badge_class;
        $array['role_icon'] = $this->role_icon;
        $array['status_badge_class'] = $this->status_badge_class;
        $array['status_text'] = $this->status_text;
        $array['status_icon'] = $this->status_icon;
        return $array;
    }

    // Update validation rules for specific user
    public static function updateValidationRules($userId)
    {
        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $userId,
            'gender' => 'required|in:Male,Female',
            'age' => 'required|integer|min:18|max:100',
            'contact_number' => 'required|regex:/^9\d{9}$/|unique:users,contact_number,' . $userId,
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:Midwife,BHW',
            'password' => 'nullable|string|min:8',
        ];
    }

    // Validation messages
    public static function validationMessages()
    {
        return [
            'name.required' => 'Name is required.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'username.max' => 'Username cannot exceed 50 characters.',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be either Male or Female.',
            'age.required' => 'Age is required.',
            'age.integer' => 'Age must be a valid number.',
            'age.min' => 'Age must be at least 18 years old.',
            'age.max' => 'Age cannot exceed 100 years.',
            'contact_number.required' => 'Contact number is required.',
            'contact_number.regex' => 'Contact number must be a valid Philippine mobile number starting with 9.',
            'contact_number.unique' => 'This contact number is already registered.',
            'address.max' => 'Address cannot exceed 500 characters.',
            'role.required' => 'Role is required.',
            'role.in' => 'Role must be either Midwife or BHW.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
        ];
    }

    // Accessor for formatted contact number
    public function getFormattedContactNumberAttribute()
    {
        return $this->contact_number ? '+63' . $this->contact_number : null;
    }

    // Accessor for role badge class
    public function getRoleBadgeClassAttribute()
    {
        return $this->role === 'Midwife' ? 'role-badge-midwife' : 'role-badge-bhw';
    }

    // Accessor for role icon
    public function getRoleIconAttribute()
    {
        return $this->role === 'Midwife' ? 'fa-user-md' : 'fa-hands-helping';
    }

    // Check if user is midwife
    public function isMidwife()
    {
        return $this->role === 'Midwife';
    }

    // Check if user is BHW
    public function isBhw()
    {
        return $this->role === 'BHW';
    }

    // Scope for filtering by role
    public function scopeByRole($query, $role)
    {
        if ($role) {
            return $query->where('role', $role);
        }
        return $query;
    }

    // Scope for filtering by gender
    public function scopeByGender($query, $gender)
    {
        if ($gender) {
            return $query->where('gender', $gender);
        }
        return $query;
    }

    // Scope for searching
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    //Scope for Deactivated users
    public function scopeDeactivated($query)
    {
        return $query->where('is_active', false);
    }

    
}