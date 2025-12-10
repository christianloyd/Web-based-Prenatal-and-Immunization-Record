<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestoreOperation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'backup_id',
        'backup_name',
        'modules_restored',
        'status',
        'progress',
        'current_step',
        'restore_options',
        'error_message',
        'started_at',
        'completed_at',
        'restored_at',
        'restored_by'
    ];

    protected $casts = [
        'modules_restored' => 'array',
        'restore_options' => 'array',
        'progress' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'restored_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the backup that was used for this restore (may be null if backup was deleted)
     */
    public function backup(): BelongsTo
    {
        return $this->belongsTo(CloudBackup::class, 'backup_id');
    }

    /**
     * Get the user who performed this restore
     */
    public function restoredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
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

        return collect($this->modules_restored)->map(function ($module) use ($moduleNames) {
            return $moduleNames[$module] ?? $module;
        })->implode(', ');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-clock'],
            self::STATUS_IN_PROGRESS => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-spinner fa-spin'],
            self::STATUS_COMPLETED => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-check-circle'],
            self::STATUS_FAILED => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'fa-times-circle'],
            default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-question-circle']
        };
    }

    /**
     * Get formatted restore options
     */
    public function getFormattedRestoreOptionsAttribute()
    {
        if (!$this->restore_options || empty($this->restore_options)) {
            return 'Standard restore';
        }

        $optionNames = [
            'create_backup' => 'Pre-restore backup created',
            'verify_integrity' => 'Integrity verified',
            'selective_restore' => 'Selective restore'
        ];

        return collect($this->restore_options)->map(function ($option) use ($optionNames) {
            return $optionNames[$option] ?? $option;
        })->implode(', ');
    }
}