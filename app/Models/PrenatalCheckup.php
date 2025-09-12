<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class PrenatalCheckup extends Model
{
    use HasFactory;

    protected $table = 'prenatal_checkups';

    protected $fillable = [
        'formatted_checkup_id',
        'prenatal_record_id',
        'patient_id',
        'checkup_date',
        'gestational_age_weeks',
        'weight_kg',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'fetal_heart_rate',
        'fundal_height_cm',
        'presentation',
        'symptoms',
        'notes',
        'next_visit_date',
        'conducted_by',
        'status',
        // Legacy fields from old structure
        'checkup_time',
        'weeks_pregnant',
        'bp_high',
        'bp_low',
        'weight',
        'baby_heartbeat',
        'belly_size',
        'baby_movement',
        'swelling',
        'next_visit_time',
        'next_visit_notes'
    ];

    protected $dates = [
        'checkup_date',
        'next_visit_date',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'checkup_date' => 'date',
        'next_visit_date' => 'date',
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

    public function conductedBy()
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['scheduled', 'upcoming'])
                     ->where('checkup_date', '>=', now()->toDateString());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('checkup_date', now()->month)
                     ->whereYear('checkup_date', now()->year);
    }

    public function scopeLastMonth($query)
    {
        return $query->whereMonth('checkup_date', now()->subMonth()->month)
                     ->whereYear('checkup_date', now()->subMonth()->year);
    }

    // Accessors
    public function getFormattedCheckupDateAttribute()
    {
        return $this->checkup_date ? $this->checkup_date->format('M d, Y') : null;
    }

    public function getFormattedNextVisitDateAttribute()
    {
        return $this->next_visit_date ? $this->next_visit_date->format('M d, Y') : null;
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
            'completed' => 'success',
            'scheduled', 'upcoming' => 'info',
            'cancelled' => 'danger',
            'rescheduled' => 'warning',
            default => 'secondary'
        };
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'completed' => 'Completed',
            'scheduled' => 'Scheduled',
            'upcoming' => 'Upcoming',
            'cancelled' => 'Cancelled',
            'rescheduled' => 'Rescheduled',
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
