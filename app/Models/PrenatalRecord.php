<?php
// app/Models/PrenatalRecord.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PrenatalRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'prenatal_records';

    /* ----------------------------------------------------------
       Mass-assignable attributes
    ---------------------------------------------------------- */
    protected $fillable = [
        'formatted_prenatal_id',
        'patient_id',
        'last_menstrual_period',
        'expected_due_date',
        'gestational_age',
        'trimester',
        'gravida',
        'para',
        'medical_history',
        'notes',
        'last_visit',
        'next_appointment',
        'status',
        'blood_pressure',
        'weight',
        'height'
    ];

    /* ----------------------------------------------------------
       Casting
    ---------------------------------------------------------- */
    protected $casts = [
        'last_menstrual_period' => 'date',
        'expected_due_date'     => 'date',
        'last_visit'            => 'date',
        'next_appointment'      => 'datetime',
        'patient_id'            => 'integer',
        'gravida'               => 'integer',
        'para'                  => 'integer',
        'trimester'             => 'integer'
    ];

        /* ----------------------------------------------------------
    Helper method to calculate gestational age in weeks and days
    ---------------------------------------------------------- */
    private function calculateGestationalAgeFromLMP($lmpDate, $currentDate = null)
    {
        if (!$lmpDate) return null;
        
        $lmp = Carbon::parse($lmpDate);
        $current = $currentDate ? Carbon::parse($currentDate) : Carbon::now();
        
        // Calculate total days since LMP
        $totalDays = $lmp->diffInDays($current);
        
        // Convert to weeks and days
        $weeks = intval($totalDays / 7);
        $days = $totalDays % 7;
        
        // Always return in consistent format: "X weeks Y days"
        if ($weeks == 0) {
            return $days == 1 ? "1 day" : "{$days} days";
        } elseif ($days == 0) {
            return $weeks == 1 ? "1 week" : "{$weeks} weeks";
        } else {
            $weekText = $weeks == 1 ? "1 week" : "{$weeks} weeks";
            $dayText = $days == 1 ? "1 day" : "{$days} days";
            return "{$weekText} {$dayText}";
        }
    }

    /* ----------------------------------------------------------
       Boot logic
    ---------------------------------------------------------- */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($record) {
            // Auto-generate formatted ID if not provided
            if (empty($record->formatted_prenatal_id)) {
                $record->formatted_prenatal_id = static::generatePrenatalId();
            }

            // Auto-calculate gestational age and trimester if LMP is provided
            if ($record->last_menstrual_period && !$record->gestational_age) {
                $record->gestational_age = $record->calculateGestationalAgeFromLMP($record->last_menstrual_period);
                
                // Calculate trimester based on whole weeks
                $lmp = Carbon::parse($record->last_menstrual_period);
                $totalDays = $lmp->diffInDays(Carbon::now());
                $gestational_weeks = intval($totalDays / 7);
                $record->trimester = $gestational_weeks <= 12 ? 1 : ($gestational_weeks <= 26 ? 2 : 3);
            }

            // Auto-calculate expected due date if not provided
            if ($record->last_menstrual_period && !$record->expected_due_date) {
                $record->expected_due_date = Carbon::parse($record->last_menstrual_period)->addDays(280);
            }
        });
    }

    /* ----------------------------------------------------------
       Helper methods
    ---------------------------------------------------------- */
    public static function generatePrenatalId()
    {
        $last = static::withTrashed()->orderByDesc('id')->first();
        return 'PR-' . str_pad(($last ? $last->id + 1 : 1), 3, '0', STR_PAD_LEFT);
    }

    /* ----------------------------------------------------------
       Scopes
    ---------------------------------------------------------- */
    public function scopeHighRisk($query)
    {
        return $query->where('status', 'high-risk');
    }

    public function scopeMonitor($query)
    {
        return $query->where('status', 'monitor');
    }

    public function scopeDue($query)
    {
        return $query->where('status', 'due');
    }

    public function scopeNormal($query)
    {
        return $query->where('status', 'normal');
    }
    
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['normal', 'monitor', 'high-risk', 'due', 'completed']);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('formatted_prenatal_id', 'like', "%{$term}%")
              ->orWhereHas('patient', function ($q) use ($term) {
                  $q->where('name', 'like', "%{$term}%")
                    ->orWhere('formatted_patient_id', 'like', "%{$term}%");
              });
    }

    /* ----------------------------------------------------------
       Computed attributes
    ---------------------------------------------------------- */
    public function getCurrentGestationalAge()
    {
        return $this->calculateGestationalAgeFromLMP($this->last_menstrual_period);
    }

    public function getCurrentTrimester()
    {
        if (!$this->last_menstrual_period) return null;
        $totalDays = $this->last_menstrual_period->diffInDays(Carbon::now());
        $weeks = intval($totalDays / 7);
        return $weeks <= 12 ? 1 : ($weeks <= 26 ? 2 : 3);
    }

    public function updateGestationalAge()
    {
        $this->gestational_age = $this->getCurrentGestationalAge();
        $this->trimester       = $this->getCurrentTrimester();
        $this->save();
    }

    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'normal'    => 'status-normal',
            'monitor'   => 'status-monitor',
            'high-risk' => 'status-high-risk',
            'due'       => 'status-due',
            'completed' => 'status-completed',
            default     => 'status-unknown',
        };
    }

    public function getStatusTextAttribute()
    {
        return ucfirst(str_replace('-', ' ', $this->status));
    }

    public function getGravidaTextAttribute()
    {
        return match ($this->gravida) {
            1 => 'G1 (First pregnancy)',
            2 => 'G2 (Second pregnancy)',
            3 => 'G3 (Third pregnancy)',
            4 => 'G4 (Fourth pregnancy)',
            5 => 'G5+ (Fifth or more)',
            default => $this->gravida ? "G{$this->gravida}" : null,
        };
    }

    public function getParaTextAttribute()
    {
        return match ($this->para) {
            0 => 'P0 (No previous births)',
            1 => 'P1 (One previous birth)',
            2 => 'P2 (Two previous births)',
            3 => 'P3 (Three previous births)',
            default => $this->para !== null ? "P{$this->para}" : null,
        };
    }

    public function getDaysUntilDueAttribute()
    {
        return $this->expected_due_date
            ? Carbon::now()->diffInDays($this->expected_due_date, false)
            : null;
    }

    public function getIsOverdueAttribute()
    {
        return $this->days_until_due < 0;
    }

    public function getWeeksPregnantAttribute()
    {
        if (!$this->last_menstrual_period) return 0;
        $totalDays = $this->last_menstrual_period->diffInDays(Carbon::now());
        return intval($totalDays / 7);
    }

    public function getIsHighRiskAttribute()
    {
        return $this->patient->age < 18 || $this->patient->age > 35 || $this->status === 'high-risk';
    }

    public function getNextAppointmentFormattedAttribute()
    {
        return optional($this->next_appointment)->format('M j, Y g:i A') ?? 'Not scheduled';
    }

    public function getLastVisitFormattedAttribute()
    {
        return optional($this->last_visit)->format('M j, Y') ?? 'No visits';
    }

    /* ----------------------------------------------------------
       Relationships
    ---------------------------------------------------------- */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointments()
    {
        return $this->hasMany(PrenatalAppointment::class);
    }

    public function visits()
    {
        return $this->hasMany(PrenatalVisit::class);
    }
    public function prenatalCheckups()
{
    return $this->hasMany(PrenatalCheckup::class);
}

public function latestCheckup()
{
    return $this->hasOne(PrenatalCheckup::class)->latest('checkup_date');
}

public function getLatestCheckupDateAttribute()
{
    $latestCheckup = $this->latestCheckup;
    return $latestCheckup ? $latestCheckup->checkup_date : null;
}

    /* ----------------------------------------------------------
       Convert to array with extras
    ---------------------------------------------------------- */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'current_gestational_age'    => $this->current_gestational_age,
            'current_trimester'          => $this->current_trimester,
            'status_text'                => $this->status_text,
            'gravida_text'               => $this->gravida_text,
            'para_text'                  => $this->para_text,
            'days_until_due'             => $this->days_until_due,
            'is_overdue'                 => $this->is_overdue,
            'is_high_risk'               => $this->is_high_risk,
            'weeks_pregnant'             => $this->weeks_pregnant,
            'next_appointment_formatted' => $this->next_appointment_formatted,
            'last_visit_formatted'       => $this->last_visit_formatted,
        ]);
    }
}