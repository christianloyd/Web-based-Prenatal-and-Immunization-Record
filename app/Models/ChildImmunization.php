<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChildImmunization extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_record_id',
        'vaccine_name',
        'vaccine_description',
        'vaccination_date',
        'administered_by',
        'batch_number',
        'notes',
        'next_due_date',
    ];

    protected $casts = [
        'vaccination_date' => 'date',
    ];

    public function childRecord()
    {
        return $this->belongsTo(ChildRecord::class);
    }
}
