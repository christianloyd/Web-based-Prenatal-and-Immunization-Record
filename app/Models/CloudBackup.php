<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CloudBackup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'format',
        'modules',
        'file_path',
        'file_size',
        'status',
        'storage_location',
        'encrypted',
        'compressed',
        'verified',
        'google_drive_file_id',
        'google_drive_link',
        'error_message',
        'started_at',
        'completed_at',
        'created_by'
    ];

    protected $casts = [
        'modules' => 'array',
        'encrypted' => 'boolean',
        'compressed' => 'boolean',
        'verified' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this backup
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get completed backups only
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get failed backups only
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get backups by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get formatted modules names
     */
    public function getFormattedModulesAttribute()
    {
        $moduleNames = [
            'patient_records' => 'Patient Records',
            'prenatal_monitoring' => 'Prenatal Monitoring',
            'child_records' => 'Child Records',
            'immunization_records' => 'Immunization Records',
            'vaccine_management' => 'Vaccine Management'
        ];

        return collect($this->modules)->map(function ($module) use ($moduleNames) {
            return $moduleNames[$module] ?? $module;
        })->implode(', ');
    }

    /**
     * Get human readable file size
     */
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size || $this->file_size === '0 MB') {
            return '0 MB';
        }

        $bytes = (float) str_replace(' MB', '', $this->file_size) * 1024 * 1024;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-check-circle'],
            'failed' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'fa-times-circle'],
            'in_progress' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-spinner'],
            'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-clock'],
            default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-question-circle']
        };
    }
}