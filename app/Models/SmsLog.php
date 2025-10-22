<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'recipient_number',
        'recipient_name',
        'message',
        'type',
        'status',
        'response',
        'related_type',
        'related_id',
        'sent_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who sent the SMS
     */
    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Get the related model (polymorphic)
     */
    public function related()
    {
        return $this->morphTo();
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
