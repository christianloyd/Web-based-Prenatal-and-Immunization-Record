<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Vaccine extends Model
{
    use HasFactory;

    protected $fillable = [
        'formatted_vaccine_id',
        'name',
        'category',
        'dosage',
        'dose_count',
        'age_schedule',
        'is_birth_dose',
        'current_stock',
        'min_stock',
        'expiry_date',
        'storage_temp',
        'notes'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'current_stock' => 'integer',
        'min_stock' => 'integer',
        'dose_count' => 'integer',
        'age_schedule' => 'array',
        'is_birth_dose' => 'boolean'
    ];

    /* ----------------------------------------------------------
       Boot logic (auto-ID)
    ---------------------------------------------------------- */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vaccine) {
            if (empty($vaccine->formatted_vaccine_id)) {
                $vaccine->formatted_vaccine_id = static::generateVaccineId();
            }
        });
    }

    /* ----------------------------------------------------------
       Helper methods
    ---------------------------------------------------------- */
    public static function generateVaccineId()
    {
        $last = static::orderByDesc('id')->first();
        return 'VC-' . str_pad(($last ? $last->id + 1 : 1), 3, '0', STR_PAD_LEFT);
    }

    // Relationships
    // Note: StockTransaction functionality has been removed

    // Accessors & Mutators
    public function getStockStatusAttribute()
    {
        if ($this->current_stock === 0) {
            return 'out-of-stock';
        } elseif ($this->current_stock <= $this->min_stock) {
            return 'low-stock';
        }
        return 'in-stock';
    }

    public function getStockStatusColorAttribute()
    {
        switch ($this->stock_status) {
            case 'out-of-stock':
                return 'bg-red-100 text-red-800';
            case 'low-stock':
                return 'bg-yellow-100 text-yellow-800';
            default:
                return 'bg-green-100 text-green-800';
        }
    }

    public function getStockStatusIconAttribute()
    {
        switch ($this->stock_status) {
            case 'out-of-stock':
                return 'fa-times-circle';
            case 'low-stock':
                return 'fa-exclamation-triangle';
            default:
                return 'fa-check-circle';
        }
    }

    public function getIsExpiringSoonAttribute()
    {
        $today = Carbon::now();
        $daysUntilExpiry = $today->diffInDays($this->expiry_date, false);
        return $daysUntilExpiry <= 30 && $daysUntilExpiry > 0;
    }

    public function getCategoryColorAttribute()
    {
        $colors = [
            'Routine Immunization' => 'bg-blue-100 text-blue-800',
            'COVID-19' => 'bg-purple-100 text-purple-800',
            'Seasonal' => 'bg-orange-100 text-orange-800',
            'Travel' => 'bg-teal-100 text-teal-800'
        ];
        return $colors[$this->category] ?? 'bg-gray-100 text-gray-800';
    }

    // Scopes
    public function scopeInStock($query)
    {
        return $query->where('current_stock', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'min_stock')
                    ->where('current_stock', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('current_stock', 0);
    }

    public function scopeExpiringSoon($query)
    {
        return $query->whereDate('expiry_date', '<=', Carbon::now()->addDays(30))
                    ->whereDate('expiry_date', '>', Carbon::now());
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('category', 'LIKE', "%{$search}%")
              ->orWhere('formatted_vaccine_id', 'LIKE', "%{$search}%");
        });
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByStockStatus($query, $status)
    {
        switch ($status) {
            case 'in-stock':
                return $query->whereColumn('current_stock', '>', 'min_stock');
            case 'low-stock':
                return $query->whereColumn('current_stock', '<=', 'min_stock')
                    ->where('current_stock', '>', 0);
            case 'out-of-stock':
                return $query->where('current_stock', 0);
            default:
                return $query;
        }
    }

    // Methods
    public function updateStock($quantity, $type, $reason)
    {
        $previousStock = $this->current_stock;

        if ($type === 'in') {
            $this->current_stock += $quantity;
        } else {
            $this->current_stock = max(0, $this->current_stock - $quantity);
        }

        $this->save();

        // Note: StockTransaction recording has been removed
        // Stock is still updated but no transaction history is kept

        return $this;
    }

    /**
     * Check if a child has completed all doses for this vaccine
     *
     * @param int $childRecordId
     * @return bool
     */
    public function isCompletedForChild($childRecordId)
    {
        if (!$this->dose_count) {
            return false;
        }

        // Count how many "Done" immunizations exist for this vaccine and child
        $completedDoses = \App\Models\Immunization::where('child_record_id', $childRecordId)
            ->where('vaccine_id', $this->id)
            ->where('status', 'Done')
            ->count();

        return $completedDoses >= $this->dose_count;
    }

    /**
     * Get remaining doses for a child
     *
     * @param int $childRecordId
     * @return int
     */
    public function getRemainingDosesForChild($childRecordId)
    {
        if (!$this->dose_count) {
            return 0;
        }

        $completedDoses = \App\Models\Immunization::where('child_record_id', $childRecordId)
            ->where('vaccine_id', $this->id)
            ->where('status', 'Done')
            ->count();

        return max(0, $this->dose_count - $completedDoses);
    }

    /**
     * Get the next dose number for a child
     *
     * @param int $childRecordId
     * @return string|null
     */
    public function getNextDoseForChild($childRecordId)
    {
        $completedDoses = \App\Models\Immunization::where('child_record_id', $childRecordId)
            ->where('vaccine_id', $this->id)
            ->where('status', 'Done')
            ->count();

        if ($completedDoses >= $this->dose_count) {
            return null; // All doses completed
        }

        $doseNumber = $completedDoses + 1;

        // Return dose label
        if ($doseNumber == 1) return '1st Dose';
        if ($doseNumber == 2) return '2nd Dose';
        if ($doseNumber == 3) return '3rd Dose';

        return $doseNumber . 'th Dose';
    }

    /* ----------------------------------------------------------
       Age Schedule Helper Methods
    ---------------------------------------------------------- */
    
    /**
     * Get the parsed age schedule array
     *
     * @return array|null
     */
    public function getAgeSchedule()
    {
        return $this->age_schedule;
    }

    /**
     * Calculate the schedule date for a specific dose based on child's birthdate
     *
     * @param Carbon|string $childBirthdate
     * @param int $doseNumber
     * @return Carbon|null
     */
    public function getScheduleDateForChild($childBirthdate, $doseNumber)
    {
        if (!$this->age_schedule || !isset($this->age_schedule['doses'])) {
            return null;
        }

        $birthdate = Carbon::parse($childBirthdate);
        
        foreach ($this->age_schedule['doses'] as $dose) {
            if ($dose['dose_number'] == $doseNumber) {
                return $this->calculateScheduleDate($birthdate, $dose['age'], $dose['unit']);
            }
        }

        return null;
    }

    /**
     * Get all schedule dates for a child based on birthdate
     *
     * @param Carbon|string $childBirthdate
     * @return array Array of ['dose_number' => int, 'label' => string, 'date' => Carbon, 'age' => string]
     */
    public function getAllScheduleDatesForChild($childBirthdate)
    {
        if (!$this->age_schedule || !isset($this->age_schedule['doses'])) {
            return [];
        }

        $birthdate = Carbon::parse($childBirthdate);
        $schedules = [];

        foreach ($this->age_schedule['doses'] as $dose) {
            $scheduleDate = $this->calculateScheduleDate($birthdate, $dose['age'], $dose['unit']);
            
            $schedules[] = [
                'dose_number' => $dose['dose_number'],
                'label' => $dose['label'],
                'date' => $scheduleDate,
                'age' => $dose['age'] . ' ' . $dose['unit'],
                'age_value' => $dose['age'],
                'age_unit' => $dose['unit']
            ];
        }

        return $schedules;
    }

    /**
     * Calculate schedule date by adding age offset to birthdate
     *
     * @param Carbon $birthdate
     * @param int $age
     * @param string $unit ('weeks', 'months', 'years')
     * @return Carbon
     */
    private function calculateScheduleDate($birthdate, $age, $unit)
    {
        $date = $birthdate->copy();
        
        switch ($unit) {
            case 'weeks':
                return $date->addWeeks($age);
            case 'months':
                return $date->addMonths($age);
            case 'years':
                return $date->addYears($age);
            default:
                return $date;
        }
    }
}