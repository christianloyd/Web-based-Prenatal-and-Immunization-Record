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
        'vaccine_id',          // New field
        'vaccine_name',        // Keep for backward compatibility during migration
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

    // Relationships
    public function childRecord()
    {
        return $this->belongsTo(ChildRecord::class);
    }

    public function vaccine()
    {
        return $this->belongsTo(Vaccine::class);
    }

    // Status options
    public static function getStatusOptions()
    {
        return [
            'Upcoming' => 'Upcoming',
            'Done' => 'Done',
            'Missed' => 'Missed'
        ];
    }

    // ADDED: Legacy vaccine types method for backward compatibility
    public static function getVaccineTypes()
    {
        return [
            'BCG' => 'BCG',
            'Hepatitis B' => 'Hepatitis B',
            'DPT' => 'DPT (Diphtheria, Pertussis, Tetanus)',
            'OPV' => 'OPV (Oral Polio Vaccine)',
            'IPV' => 'IPV (Inactivated Polio Vaccine)',
            'MMR' => 'MMR (Measles, Mumps, Rubella)',
            'Varicella' => 'Varicella (Chickenpox)',
            'Pneumococcal' => 'Pneumococcal',
            'Rotavirus' => 'Rotavirus',
            'Influenza' => 'Influenza (Flu)',
            'COVID-19' => 'COVID-19'
        ];
    }

    // Get available vaccines from inventory (replaces static getVaccineTypes)
    public static function getAvailableVaccines()
    {
        return Vaccine::inStock()
                     ->select('id', 'name', 'current_stock', 'category')
                     ->orderBy('name')
                     ->get()
                     ->mapWithKeys(function ($vaccine) {
                         return [$vaccine->id => $vaccine->name . " (Stock: {$vaccine->current_stock})"];
                     });
    }

    // Get vaccines that can be scheduled (including low stock but not out of stock)
    public static function getSchedulableVaccines()
    {
        return Vaccine::where('current_stock', '>', 0)
                     ->select('id', 'name', 'current_stock', 'category')
                     ->orderBy('name')
                     ->get();
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

    // Get vaccine name (use relationship if available, fallback to string field)
    public function getVaccineNameAttribute()
    {
        if ($this->vaccine) {
            return $this->vaccine->name;
        }
        return $this->attributes['vaccine_name'] ?? 'Unknown Vaccine';
    }

    // Check if vaccine is available for scheduling
    public function isVaccineAvailable()
    {
        if (!$this->vaccine) {
            return false;
        }
        return $this->vaccine->current_stock > 0;
    }

    // Consume vaccine stock when marking as done
    public function consumeVaccineStock($reason = 'Immunization administered')
    {
        if (!$this->vaccine || $this->vaccine->current_stock <= 0) {
            throw new \Exception('Vaccine not available in stock');
        }

        $this->vaccine->updateStock(1, 'out', $reason);
        return $this;
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
        if ($this->status === 'Done' || $this->status === 'Missed') {
            return false;
        }
        
        return $this->schedule_date < now()->toDateString();
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'Upcoming')
                     ->where('schedule_date', '>=', now()->toDateString());
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'Upcoming')
                     ->where('schedule_date', '<', now()->toDateString());
    }
    
    public function scopeDone($query)
    {
        return $query->where('status', 'Done');
    }

    // Scope to include vaccine relationship
    public function scopeWithVaccine($query)
    {
        return $query->with('vaccine');
    }
}