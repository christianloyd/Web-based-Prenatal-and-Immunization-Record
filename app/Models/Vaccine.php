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
        'dose_count' => 'integer'
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
                return $query->whereRaw('current_stock > min_stock');
            case 'low-stock':
                return $query->whereRaw('current_stock <= min_stock AND current_stock > 0');
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
}