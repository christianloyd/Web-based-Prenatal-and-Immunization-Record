<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'vaccine_id',
        'transaction_type',
        'quantity',
        'previous_stock',
        'new_stock',
        'reason'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_stock' => 'integer',
        'new_stock' => 'integer'
    ];

    // Relationships
    public function vaccine()
    {
        return $this->belongsTo(Vaccine::class);
    }

    // Accessors
    public function getTransactionTypeTextAttribute()
    {
        return $this->transaction_type === 'in' ? 'Stock In' : 'Stock Out';
    }

    public function getTransactionTypeColorAttribute()
    {
        return $this->transaction_type === 'in' ? 'text-green-600' : 'text-red-600';
    }

    public function getTransactionTypeIconAttribute()
    {
        return $this->transaction_type === 'in' ? 'fa-plus-circle' : 'fa-minus-circle';
    }

    // Scopes
    public function scopeRecent($query, $limit = 10)
    {
        return $query->latest()->limit($limit);
    }

    public function scopeForVaccine($query, $vaccineId)
    {
        return $query->where('vaccine_id', $vaccineId);
    }

    public function scopeStockIn($query)
    {
        return $query->where('transaction_type', 'in');
    }

    public function scopeStockOut($query)
    {
        return $query->where('transaction_type', 'out');
    }
}