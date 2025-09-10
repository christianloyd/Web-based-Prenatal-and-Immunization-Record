<?php
// app/Models/PrenatalAppointment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PrenatalAppointment extends Model
{
    protected $fillable = [
        'formatted_patient_id',
        'appointment_date',
        'appointment_type',
        'notes',
        'status'
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
    ];

    // Relationships
    public function prenatalRecord()
    {
        return $this->belongsTo(PrenatalRecord::class, 'formatted_patient_id', 'formatted_patient_id');
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', Carbon::now())
                    ->where('status', 'scheduled');
    }

    public function scopeOverdue($query)
    {
        return $query->where('appointment_date', '<', Carbon::now())
                    ->where('status', 'scheduled');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('appointment_type', $type);
    }

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->appointment_date->format('M d, Y g:i A');
    }

    public function getIsOverdueAttribute()
    {
        return $this->appointment_date->isPast() && $this->status === 'scheduled';
    }

    // Methods
    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->save();
    }

    public function markAsCancelled()
    {
        $this->status = 'cancelled';
        $this->save();
    }

    public function markAsNoShow()
    {
        $this->status = 'no-show';
        $this->save();
    }
}

 