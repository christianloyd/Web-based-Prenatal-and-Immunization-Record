<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Audit Logger Service
 *
 * Provides a simple interface for logging security and operational events.
 */
class AuditLogger
{
    /**
     * Log a generic event.
     *
     * @param string $event Event name (e.g., 'user.login', 'patient.created')
     * @param string $action Action performed (create, update, delete, etc.)
     * @param mixed $auditable The model being audited (optional)
     * @param array $oldValues Previous state (optional)
     * @param array $newValues New state (optional)
     * @param string $severity Severity level (low, medium, high, critical)
     * @param array $metadata Additional context (optional)
     * @return \App\Models\AuditLog
     */
    public static function log(
        string $event,
        string $action,
        $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        string $severity = 'low',
        array $metadata = []
    ): AuditLog {
        $user = Auth::user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_role' => $user?->role,
            'event' => $event,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->id,
            'action' => $action,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'severity' => $severity,
        ]);
    }

    /**
     * Log a successful login attempt.
     *
     * @param \App\Models\User $user
     * @return \App\Models\AuditLog
     */
    public static function logLogin($user): AuditLog
    {
        return self::log(
            event: 'auth.login',
            action: 'login',
            severity: 'low',
            metadata: [
                'user_id' => $user->id,
                'username' => $user->username ?? $user->email,
            ]
        );
    }

    /**
     * Log a failed login attempt.
     *
     * @param string $identifier Username or email attempted
     * @return \App\Models\AuditLog
     */
    public static function logFailedLogin(string $identifier): AuditLog
    {
        return self::log(
            event: 'auth.login.failed',
            action: 'login_failed',
            severity: 'medium',
            metadata: [
                'identifier' => $identifier,
                'ip_address' => Request::ip(),
            ]
        );
    }

    /**
     * Log a logout event.
     *
     * @param \App\Models\User $user
     * @return \App\Models\AuditLog
     */
    public static function logLogout($user): AuditLog
    {
        return self::log(
            event: 'auth.logout',
            action: 'logout',
            severity: 'low',
            metadata: [
                'user_id' => $user->id,
            ]
        );
    }

    /**
     * Log user creation.
     *
     * @param \App\Models\User $user
     * @return \App\Models\AuditLog
     */
    public static function logUserCreated($user): AuditLog
    {
        return self::log(
            event: 'user.created',
            action: 'create',
            auditable: $user,
            newValues: $user->only(['name', 'username', 'role', 'is_active']),
            severity: 'medium'
        );
    }

    /**
     * Log user update.
     *
     * @param \App\Models\User $user
     * @param array $oldValues
     * @return \App\Models\AuditLog
     */
    public static function logUserUpdated($user, array $oldValues): AuditLog
    {
        return self::log(
            event: 'user.updated',
            action: 'update',
            auditable: $user,
            oldValues: $oldValues,
            newValues: $user->only(['name', 'username', 'role', 'is_active']),
            severity: 'medium'
        );
    }

    /**
     * Log user deletion.
     *
     * @param \App\Models\User $user
     * @return \App\Models\AuditLog
     */
    public static function logUserDeleted($user): AuditLog
    {
        return self::log(
            event: 'user.deleted',
            action: 'delete',
            auditable: $user,
            oldValues: $user->only(['name', 'username', 'role']),
            severity: 'high'
        );
    }

    /**
     * Log patient data access.
     *
     * @param mixed $patient
     * @param string $accessType View, export, print, etc.
     * @return \App\Models\AuditLog
     */
    public static function logPatientAccess($patient, string $accessType = 'view'): AuditLog
    {
        return self::log(
            event: 'patient.accessed',
            action: $accessType,
            auditable: $patient,
            severity: 'low',
            metadata: [
                'patient_id' => $patient->id,
                'patient_name' => $patient->name,
                'access_type' => $accessType,
            ]
        );
    }

    /**
     * Log sensitive data modification.
     *
     * @param string $event
     * @param mixed $model
     * @param array $oldValues
     * @param array $newValues
     * @return \App\Models\AuditLog
     */
    public static function logSensitiveChange(
        string $event,
        $model,
        array $oldValues,
        array $newValues
    ): AuditLog {
        return self::log(
            event: $event,
            action: 'update',
            auditable: $model,
            oldValues: $oldValues,
            newValues: $newValues,
            severity: 'high'
        );
    }

    /**
     * Log security event (unauthorized access, permission denied, etc.).
     *
     * @param string $event
     * @param string $description
     * @param string $severity
     * @return \App\Models\AuditLog
     */
    public static function logSecurityEvent(
        string $event,
        string $description,
        string $severity = 'high'
    ): AuditLog {
        return self::log(
            event: $event,
            action: 'security_event',
            severity: $severity,
            metadata: [
                'description' => $description,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]
        );
    }

    /**
     * Log data export.
     *
     * @param string $exportType PDF, Excel, etc.
     * @param array $metadata
     * @return \App\Models\AuditLog
     */
    public static function logDataExport(string $exportType, array $metadata = []): AuditLog
    {
        return self::log(
            event: 'data.exported',
            action: 'export',
            severity: 'medium',
            metadata: array_merge([
                'export_type' => $exportType,
            ], $metadata)
        );
    }

    /**
     * Log backup operations.
     *
     * @param string $action create, restore, delete
     * @param array $metadata
     * @return \App\Models\AuditLog
     */
    public static function logBackupOperation(string $action, array $metadata = []): AuditLog
    {
        return self::log(
            event: 'backup.' . $action,
            action: $action,
            severity: 'high',
            metadata: $metadata
        );
    }
}
