<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'formatted_appointment_id',
        'patient_id',
        'prenatal_record_id',
        'appointment_date',
        'appointment_time',
        'type',
        'status',
        'conducted_by',
        'notes',
        'cancellation_reason',
        'rescheduled_from_date',
        'rescheduled_from_time'
    ];

    protected $dates = [
        'appointment_date',
        'rescheduled_from_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'rescheduled_from_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'rescheduled_from_time' => 'datetime:H:i',
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function prenatalRecord()
    {
        return $this->belongsTo(PrenatalRecord::class);
    }

    public function conductedBy()
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    public function prenatalCheckup()
    {
        return $this->hasOne(PrenatalCheckup::class);
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['scheduled'])
                     ->where('appointment_date', '>=', now()->toDateString());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', now()->toDateString());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('appointment_date', [
            now()->startOfWeek()->toDateString(),
            now()->endOfWeek()->toDateString()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('appointment_date', now()->month)
                     ->whereYear('appointment_date', now()->year);
    }

    public function scopePrenatalCheckups($query)
    {
        return $query->where('type', 'prenatal_checkup');
    }

    // Accessors
    public function getFormattedAppointmentDateAttribute()
    {
        return $this->appointment_date ? $this->appointment_date->format('M d, Y') : null;
    }

    public function getFormattedAppointmentTimeAttribute()
    {
        return $this->appointment_time ? Carbon::parse($this->appointment_time)->format('g:i A') : null;
    }

    public function getFormattedAppointmentDateTimeAttribute()
    {
        if ($this->appointment_date && $this->appointment_time) {
            return $this->appointment_date->format('M d, Y') . ' at ' . Carbon::parse($this->appointment_time)->format('g:i A');
        }
        return null;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'completed' => 'success',
            'scheduled' => 'info',
            'cancelled' => 'danger',
            'rescheduled' => 'warning',
            'no_show' => 'secondary',
            default => 'secondary'
        };
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'completed' => 'Completed',
            'scheduled' => 'Scheduled',
            'cancelled' => 'Cancelled',
            'rescheduled' => 'Rescheduled',
            'no_show' => 'No Show',
            default => ucfirst($this->status)
        };
    }

    public function getTypeTextAttribute()
    {
        return match($this->type) {
            'prenatal_checkup' => 'Prenatal Checkup',
            'follow_up' => 'Follow Up',
            'consultation' => 'Consultation',
            'emergency' => 'Emergency',
            default => ucfirst(str_replace('_', ' ', $this->type))
        };
    }

    // Mutators
    public function setAppointmentDateAttribute($value)
    {
        $this->attributes['appointment_date'] = $value ? Carbon::parse($value)->toDateString() : null;
    }

    public function setAppointmentTimeAttribute($value)
    {
        $this->attributes['appointment_time'] = $value ? Carbon::parse($value)->format('H:i:s') : null;
    }

    // Boot method for auto-generating formatted IDs
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            if (!$appointment->formatted_appointment_id) {
                $lastAppointment = static::withTrashed()->orderBy('id', 'desc')->first();
                $nextId = $lastAppointment ? $lastAppointment->id + 1 : 1;
                $appointment->formatted_appointment_id = 'APT' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Helper methods
    public function canBeCancelled()
    {
        return in_array($this->status, ['scheduled']) && $this->appointment_date >= now()->toDateString();
    }

    public function canBeRescheduled()
    {
        return in_array($this->status, ['scheduled']) && $this->appointment_date >= now()->toDateString();
    }

    public function canBeCompleted()
    {
        return $this->status === 'scheduled';
    }

    public function markAsCompleted($conductedBy = null)
    {
        $this->update([
            'status' => 'completed',
            'conducted_by' => $conductedBy ?? auth()->id()
        ]);
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason
        ]);
    }

    public function reschedule($newDate, $newTime, $reason = null)
    {
        $this->update([
            'rescheduled_from_date' => $this->appointment_date,
            'rescheduled_from_time' => $this->appointment_time,
            'appointment_date' => $newDate,
            'appointment_time' => $newTime,
            'status' => 'scheduled',
            'notes' => $reason ? ($this->notes ? $this->notes . "\n\nRescheduled: " . $reason : "Rescheduled: " . $reason) : $this->notes
        ]);
    }
}
