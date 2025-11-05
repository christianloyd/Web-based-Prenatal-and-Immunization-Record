<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PrenatalCheckup extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'prenatal_checkups';

    protected $fillable = [
        'formatted_checkup_id',
        'prenatal_record_id',
        'patient_id',
        'appointment_id',
        'gestational_age_weeks',
        'weight_kg',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'fetal_heart_rate',
        'fundal_height_cm',
        'presentation',
        'symptoms',
        'notes',
        'status',
        // Legacy fields - kept for backward compatibility during transition
        'checkup_date',
        'checkup_time',
        'weeks_pregnant',
        'bp_high',
        'bp_low',
        'weight',
        'baby_heartbeat',
        'belly_size',
        'baby_movement',
        'swelling',
        'next_visit_date',
        'next_visit_time',
        'next_visit_notes',
        'conducted_by',
        // Missed checkup tracking fields
        'missed_date',
        'missed_reason',
        'auto_missed',
        // Reschedule tracking fields
        'rescheduled',
        'rescheduled_to_checkup_id'
    ];

    protected $dates = [
        'checkup_date',
        'next_visit_date',
        'missed_date',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'checkup_date' => 'date',
        'next_visit_date' => 'date',
        'missed_date' => 'datetime',
        'weight_kg' => 'decimal:2',
        'fundal_height_cm' => 'decimal:1',
        'swelling' => 'json',
    ];

    // Relationships
    public function prenatalRecord()
    {
        return $this->belongsTo(PrenatalRecord::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function conductedBy()
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    // Scopes
    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereHas('appointment', function($q) {
            $q->whereMonth('appointment_date', now()->month)
              ->whereYear('appointment_date', now()->year);
        });
    }

    public function scopeLastMonth($query)
    {
        return $query->whereHas('appointment', function($q) {
            $q->whereMonth('appointment_date', now()->subMonth()->month)
              ->whereYear('appointment_date', now()->subMonth()->year);
        });
    }

    // Accessors
    public function getFormattedCheckupDateAttribute()
    {
        // Get date from appointment if available, otherwise use legacy field
        if ($this->appointment) {
            return $this->appointment->formatted_appointment_date;
        }
        return $this->checkup_date ? $this->checkup_date->format('M d, Y') : null;
    }

    public function getFormattedCheckupTimeAttribute()
    {
        // Get time from appointment if available, otherwise use legacy field
        if ($this->appointment) {
            return $this->appointment->formatted_appointment_time;
        }
        return $this->checkup_time ? Carbon::parse($this->checkup_time)->format('g:i A') : null;
    }

    public function getFormattedCheckupDateTimeAttribute()
    {
        if ($this->appointment) {
            return $this->appointment->formatted_appointment_date_time;
        }
        // Fallback to legacy fields
        if ($this->checkup_date && $this->checkup_time) {
            return $this->checkup_date->format('M d, Y') . ' at ' . Carbon::parse($this->checkup_time)->format('g:i A');
        }
        return null;
    }

    public function getBloodPressureAttribute()
    {
        if ($this->blood_pressure_systolic && $this->blood_pressure_diastolic) {
            return $this->blood_pressure_systolic . '/' . $this->blood_pressure_diastolic;
        }
        // Fallback to legacy format
        if ($this->bp_high && $this->bp_low) {
            return $this->bp_high . '/' . $this->bp_low;
        }
        return null;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'done' => 'success',
            'upcoming' => 'primary',
            default => 'secondary'
        };
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'done' => 'Done',
            'upcoming' => 'Upcoming',
            default => ucfirst($this->status)
        };
    }

    // Mutators
    public function setCheckupDateAttribute($value)
    {
        $this->attributes['checkup_date'] = $value ? Carbon::parse($value)->toDateString() : null;
    }

    public function setNextVisitDateAttribute($value)
    {
        $this->attributes['next_visit_date'] = $value ? Carbon::parse($value)->toDateString() : null;
    }

    // Boot method for auto-generating formatted IDs
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($checkup) {
            if (!$checkup->formatted_checkup_id) {
                $lastCheckup = static::withTrashed()->orderBy('id', 'desc')->first();
                $nextId = $lastCheckup ? $lastCheckup->id + 1 : 1;
                $checkup->formatted_checkup_id = 'PC' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
