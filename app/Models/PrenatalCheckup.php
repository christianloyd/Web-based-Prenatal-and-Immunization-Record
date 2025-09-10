<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PrenatalRecord;
use Carbon\Carbon;

class PrenatalCheckup extends Model
{
    protected $fillable = [
        'formatted_checkup_id',
        'patient_id',
        'prenatal_record_id',
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
        'notes',
        'next_visit_date',
        'next_visit_time',
        'next_visit_notes',
        'conducted_by',
        'status'
    ];

    protected $casts = [
        'checkup_date' => 'date',
        'next_visit_date' => 'date',
        'swelling' => 'array',
    ];

    /* ----------------------------------------------------------
       Boot logic (auto-ID)
    ---------------------------------------------------------- */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($checkup) {
            if (empty($checkup->formatted_checkup_id)) {
                $checkup->formatted_checkup_id = static::generateCheckupId();
            }
        });
    }

    /* ----------------------------------------------------------
       Helper methods
    ---------------------------------------------------------- */
    public static function generateCheckupId()
    {
        $last = static::orderByDesc('id')->first();
        return 'CK-' . str_pad(($last ? $last->id + 1 : 1), 3, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function prenatalRecord()
    {
        return $this->belongsTo(PrenatalRecord::class);
    }

    /**
     * Relationship with patient
     */
    

    /**
     * Get blood pressure as formatted string
     */
    public function getBloodPressureAttribute()
    {
        if ($this->bp_high && $this->bp_low) {
            return $this->bp_high . '/' . $this->bp_low;
        }
        return null;
    }

    /**
     * Get swelling locations as formatted string
     */
    public function getSwellingTextAttribute()
    {
        if (!$this->swelling || empty($this->swelling)) {
            return 'None';
        }
        
        if (in_array('none', $this->swelling)) {
            return 'None';
        }
        
        return ucfirst(implode(', ', $this->swelling));
    }

    /**
     * Scope for completed checkups
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for scheduled checkups
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
 * Calculate weeks pregnant (weeks only, no days)
 * Based on patient's LMP and checkup date
 */
/**
 * Calculate weeks pregnant (weeks only, no days)
 * Based on patient's LMP and checkup date
 */
// Replace the existing calculateWeeksPregnant method
/**
 * Calculate weeks pregnant (weeks only, no days)
 * Based on patient's LMP and checkup date
 */
public function calculateWeeksPregnant()
{
    if (!$this->patient || !$this->patient->activePrenatalRecord) {
        return null;
    }

    $lmp = $this->patient->activePrenatalRecord->last_menstrual_period;
    if (!$lmp) {
        return null;
    }

    // Calculate total days between LMP and checkup date
    $totalDays = Carbon::parse($lmp)->diffInDays(Carbon::parse($this->checkup_date));
    
    // Convert to whole weeks only (no decimals)
    $weeks = intval($totalDays / 7);
    
    // Format properly
    return $weeks == 1 ? "1 week" : "{$weeks} weeks";
}

/**
 * Get weeks pregnant as integer only
 */
public function getWeeksPregnantNumberAttribute()
{
    if (!$this->patient || !$this->patient->activePrenatalRecord) {
        return 0;
    }

    $lmp = $this->patient->activePrenatalRecord->last_menstrual_period;
    if (!$lmp) {
        return 0;
    }

    // Calculate total days between LMP and checkup date
    $totalDays = Carbon::parse($lmp)->diffInDays(Carbon::parse($this->checkup_date));
    
    // Return whole weeks only
    return intval($totalDays / 7);
}
 

}