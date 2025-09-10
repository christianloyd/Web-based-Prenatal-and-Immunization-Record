<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Immunization extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_record_id',
        'vaccine_name',
        'dose',
        'schedule_date',
        'schedule_time',
        'status',
        'administered_date',
        'administered_by',
        'notes',
        'next_due_date'
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'administered_date' => 'date',
        'schedule_time' => 'datetime:H:i'
    ];

    // Relationship with ChildRecord
    public function childRecord()
    {
        return $this->belongsTo(ChildRecord::class);
    }

    // Status options
    public static function getStatusOptions()
    {
        return [
            'Scheduled' => 'Scheduled',
            'Completed' => 'Completed',
            'Missed' => 'Missed',
            'Cancelled' => 'Cancelled'
        ];
    }

    // Common vaccine types
    public static function getVaccineTypes()
    {
        return [
            'BCG' => 'BCG (Tuberculosis)',
            'Hepatitis B' => 'Hepatitis B',
            'DPT' => 'DPT (Diphtheria, Pertussis, Tetanus)',
            'OPV' => 'OPV (Oral Polio Vaccine)',
            'IPV' => 'IPV (Inactivated Polio Vaccine)',
            'Hib' => 'Hib (Haemophilus influenzae type b)',
            'PCV' => 'PCV (Pneumococcal)',
            'MMR' => 'MMR (Measles, Mumps, Rubella)',
            'Varicella' => 'Varicella (Chickenpox)',
            'Hepatitis A' => 'Hepatitis A',
            'Influenza' => 'Influenza (Seasonal Flu)',
            'Td' => 'Td (Tetanus-Diphtheria)',
            'Tdap' => 'Tdap (Tetanus-Diphtheria-Pertussis)'
        ];
    }

    // Dose options
    public static function getDoseOptions()
    {
        return [
            '1st Dose' => '1st Dose',
            '2nd Dose' => '2nd Dose',
            '3rd Dose' => '3rd Dose',
            '4th Dose' => '4th Dose',
            'Booster' => 'Booster',
            'Annual' => 'Annual'
        ];
    }

    // Calculate age at vaccination
    public function getAgeAtVaccinationAttribute()
    {
        if (!$this->childRecord || !$this->childRecord->birthdate || !$this->schedule_date) {
            return null;
        }

        $birthDate = $this->childRecord->birthdate;
        $scheduleDate = $this->schedule_date;
        
        $ageInMonths = $birthDate->diffInMonths($scheduleDate);
        
        if ($ageInMonths < 12) {
            return $ageInMonths . ' month' . ($ageInMonths != 1 ? 's' : '');
        } else {
            $years = floor($ageInMonths / 12);
            $months = $ageInMonths % 12;
            
            $result = $years . ' year' . ($years != 1 ? 's' : '');
            if ($months > 0) {
                $result .= ' ' . $months . ' month' . ($months != 1 ? 's' : '');
            }
            
            return $result;
        }
    }

    // Check if immunization is overdue
    public function getIsOverdueAttribute()
    {
        if ($this->status === 'Completed' || $this->status === 'Cancelled') {
            return false;
        }
        
        return $this->schedule_date < now()->toDateString();
    }

    // Scope for filtering by status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope for upcoming immunizations
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'Scheduled')
                    ->where('schedule_date', '>=', now()->toDateString());
    }

    // Scope for overdue immunizations
    public function scopeOverdue($query)
    {
        return $query->where('status', 'Scheduled')
                    ->where('schedule_date', '<', now()->toDateString());
    }
}