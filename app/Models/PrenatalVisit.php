<?php
// app/Models/PrenatalVisit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PrenatalVisit extends Model
{
    protected $fillable = [
        'formatted_patient_id',
        'visit_date',
        'gestational_age',
        'weight',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'fetal_heart_rate',
        'fundal_height',
        'complaints',
        'physical_exam',
        'lab_results',
        'recommendations',
        'notes',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
    ];

    // Relationships
    public function prenatalRecord()
    {
        return $this->belongsTo(PrenatalRecord::class, 'formatted_patient_id', 'formatted_patient_id');
    }

    // Scopes
    public function scopeRecent($query)
    {
        return $query->orderBy('visit_date', 'desc');
    }

    public function scopeByGestationalAge($query, $weeks)
    {
        return $query->where('gestational_age', 'LIKE', "%{$weeks}%");
    }

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->visit_date->format('M d, Y');
    }

    public function getBloodPressureAttribute()
    {
        return "{$this->blood_pressure_systolic}/{$this->blood_pressure_diastolic} mmHg";
    }

    public function getIsNormalVisitAttribute()
    {
        return $this->complaints === 'None' 
            && $this->blood_pressure_systolic < 140 
            && $this->blood_pressure_diastolic < 90;
    }

    // Methods
    public function summary()
    {
        return "Visit on {$this->formatted_date}: "
             . "GA {$this->gestational_age}, "
             . "BP {$this->blood_pressure}, "
             . "FHR {$this->fetal_heart_rate} bpm.";
    }
}
