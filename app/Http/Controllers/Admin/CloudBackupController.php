<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CloudBackup;
use App\Models\RestoreOperation;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;
use Exception;

class CloudBackupController extends Controller
{
    private DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Check if current user is admin
     */
    private function checkAdminAccess()
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Admin access required');
        }
    }

    /**
     * Display the cloud backup management page
     */
    public function index()
    {
        $this->checkAdminAccess();
        $stats = $this->backupService->getBackupStats();
        $googleDriveConnected = $this->backupService->testGoogleDriveConnection();
        $driveStorage = $this->backupService->getGoogleDriveStorageInfo();
        $moduleInfo = $this->backupService->getModuleInfo();

        // Check if using OAuth2 and if authenticated
        $googleDriveService = app(\App\Services\GoogleDriveService::class);
        $isOAuth = file_exists(storage_path('app/google/oauth_credentials.json'));
        $isAuthenticated = $googleDriveService ? $googleDriveService->isAuthenticated() : false;

        return view('admin.cloudbackup.index', compact(
            'stats',
            'googleDriveConnected',
            'driveStorage',
            'moduleInfo',
            'isOAuth',
            'isAuthenticated'
        ));
    }

    /**
     * Get backup data for AJAX requests
     */
    public function getData(Request $request)
    {
        $this->checkAdminAccess();
        try {
            $query = CloudBackup::with('creator')->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->has('type') && $request->type !== '') {
                $query->where('type', $request->type);
            }

            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            $backups = $query->get()->map(function ($backup) {
                return [
                    'id' => $backup->id,
                    'name' => $backup->name ?? 'Unnamed Backup',
                    'type' => $backup->type ?? 'unknown',
                    'format' => $backup->format ?? 'sql_dump',
                    'modules' => $backup->modules ?? [],
                    'formatted_modules' => $backup->formatted_modules ?? '',
                    'size' => $backup->file_size ?: '0 MB',
                    'status' => $backup->status ?? 'pending',
                    'created_at' => $backup->created_at ? $backup->created_at->toISOString() : now()->toISOString(),
                    'storage_location' => $backup->storage_location ?? 'local',
                    'encrypted' => $backup->encrypted ?? false,
                    'compressed' => $backup->compressed ?? false,
                    'error' => $backup->error_message,
                    'status_badge' => $backup->status_badge ?? '',
                    'google_drive_file_id' => $backup->google_drive_file_id,
                    'google_drive_link' => $backup->google_drive_link,
                    'creator' => $backup->creator ? $backup->creator->name : 'Unknown'
                ];
            });

            // Get restore operations with error handling
            $restores = [];
            try {
                $restoreQuery = RestoreOperation::with(['restoredBy'])->orderBy('restored_at', 'desc');

                // Apply filters for restores
                if ($request->has('status') && $request->status !== '') {
                    $restoreQuery->where('status', $request->status);
                }

                $restores = $restoreQuery->get()->map(function ($restore) {
                    return [
                        'id' => $restore->id,
                        'backup_name' => $restore->backup_name ?? 'Unknown Backup',
                        'backup_id' => $restore->backup_id,
                        'modules_restored' => $restore->modules_restored ?? [],
                        'formatted_modules' => $restore->formatted_modules ?? '',
                        'status' => $restore->status ?? 'unknown',
                        'restore_options' => $restore->formatted_restore_options ?? '',
                        'restored_at' => $restore->restored_at ? $restore->restored_at->toISOString() : now()->toISOString(),
                        'error' => $restore->error_message,
                        'status_badge' => $restore->status_badge ?? '',
                        'restored_by' => $restore->restoredBy ? $restore->restoredBy->name : 'Unknown'
                    ];
                });
            } catch (Exception $e) {
                \Log::warning('Failed to load restore operations: ' . $e->getMessage());
                $restores = [];
            }

            return response()->json([
                'backups' => $backups,
                'restores' => $restores,
                'stats' => $this->backupService->getBackupStats()
            ]);

        } catch (Exception $e) {
            \Log::error('Failed to load backup data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load backup data',
                'error' => $e->getMessage(),
                'backups' => [],
                'restores' => [],
                'stats' => [
                    'total_backups' => 0,
                    'successful_backups' => 0,
                    'last_backup' => 'Never',
                    'storage_used' => '0 MB'
                ]
            ], 500);
        }
    }

    /**
     * Create a new backup
     */
    public function store(Request $request)
    {
        $this->checkAdminAccess();
        $request->validate([
            'backup_name' => 'nullable|string|max:255',
            'modules' => 'required|array|min:1',
            'modules.*' => 'in:patient_records,prenatal_monitoring,child_records,immunization_records,vaccine_management',
            'options' => 'nullable|array',
            'options.*' => 'in:compress,encrypt,verify'
        ]);

        try {
            $modules = $request->modules;
            $options = $request->options ?? [];

            // Generate backup name if not provided
            $backupName = $request->backup_name ?: $this->generateBackupName($modules);

            // Determine backup type
            $allModules = ['patient_records', 'prenatal_monitoring', 'child_records', 'immunization_records', 'vaccine_management'];
            $backupType = count($modules) === count($allModules) ? 'full' : 'selective';

            // Create backup record
            $backup = CloudBackup::create([
                'name' => $backupName,
                'type' => $backupType,
                'format' => 'sql_dump',
                'modules' => $modules,
                'status' => 'pending',
                'storage_location' => 'google_drive',
                'encrypted' => in_array('encrypt', $options),
                'compressed' => in_array('compress', $options),
                'verified' => in_array('verify', $options),
                'created_by' => Auth::id()
            ]);

            // Queue the backup job (for now, we'll process it synchronously)
            $this->backupService->createBackup($backup);

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully!',
                'backup_id' => $backup->id
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get backup progress (for real-time updates)
     */
    public function progress($id)
    {
        $backup = CloudBackup::findOrFail($id);

        return response()->json([
            'status' => $backup->status,
            'progress' => $this->calculateProgress($backup),
            'message' => $this->getStatusMessage($backup),
            'error' => $backup->error_message
        ]);
    }

    /**
     * Download a backup file
     */
    public function download($id)
    {
        try {
            $backup = CloudBackup::findOrFail($id);

            if ($backup->status !== 'completed') {
                return response()->json(['error' => 'Backup is not completed'], 400);
            }

            return $this->backupService->downloadBackup($backup);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Restore from a backup
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_id' => 'required|exists:cloud_backups,id',
            'restore_options' => 'nullable|array',
            'restore_options.*' => 'in:create_backup,verify_integrity',
            'confirm_restore' => 'required|accepted'
        ]);

        try {
            $backup = CloudBackup::findOrFail($request->backup_id);

            \Log::info('Admin restore request received', [
                'backup_id' => $request->backup_id,
                'restore_options' => $request->restore_options,
                'backup_name' => $backup->name,
                'admin_user' => Auth::user()->name
            ]);

            if ($backup->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot restore from incomplete backup'
                ], 400);
            }

            // Create backup before restore if requested
            if (in_array('create_backup', $request->restore_options ?? [])) {
                $preRestoreBackup = CloudBackup::create([
                    'name' => 'Pre-restore Backup ' . now()->format('Y-m-d H:i:s'),
                    'type' => 'full',
                    'format' => 'sql_dump',
                    'modules' => ['patient_records', 'prenatal_monitoring', 'child_records', 'immunization_records', 'vaccine_management'],
                    'status' => 'pending',
                    'storage_location' => 'google_drive',
                    'encrypted' => true,
                    'compressed' => true,
                    'verified' => true,
                    'created_by' => Auth::id()
                ]);

                $this->backupService->createBackup($preRestoreBackup);
                $backup->refresh();
            }

            // Verify backup integrity if requested
            if (in_array('verify_integrity', $request->restore_options ?? [])) {
                $integrityCheck = $this->backupService->verifyBackupIntegrity($backup);

                if (!$integrityCheck['valid']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Backup integrity verification failed: ' . $integrityCheck['error']
                    ], 400);
                }
            }

            $backup->refresh();

            if ($backup->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot restore from incomplete backup. Current status: ' . $backup->status
                ], 400);
            }

            // Perform the restore
            $this->backupService->restoreBackup($backup);

            // Create restore operation record after successful restore
            RestoreOperation::create([
                'backup_id' => $backup->id,
                'backup_name' => $backup->name,
                'modules_restored' => $backup->modules,
                'status' => 'completed',
                'restore_options' => $request->restore_options ?? [],
                'restored_at' => now(),
                'restored_by' => Auth::id()
            ]);

            $restoreMessage = 'Data restored successfully from "' . $backup->name . '"!';
            if ($backup->type === 'selective') {
                $modules = is_array($backup->modules) ? $backup->modules : [];
                $moduleNames = array_map(function($module) {
                    return str_replace('_', ' ', ucwords($module, '_'));
                }, $modules);
                $restoreMessage .= ' Only the following modules were restored: ' . implode(', ', $moduleNames) . '. Other data was preserved.';
            }

            return response()->json([
                'success' => true,
                'message' => $restoreMessage
            ]);

        } catch (Exception $e) {
            // Create failed restore operation record
            if (isset($backup)) {
                RestoreOperation::create([
                    'backup_id' => $backup->id,
                    'backup_name' => $backup->name,
                    'modules_restored' => $backup->modules,
                    'status' => 'failed',
                    'restore_options' => $request->restore_options ?? [],
                    'error_message' => $e->getMessage(),
                    'restored_at' => now(),
                    'restored_by' => Auth::id()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a backup
     */
    public function destroy($id)
    {
        try {
            $backup = CloudBackup::find($id);

            if (!$backup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup not found.'
                ], 404);
            }

            $backupName = $backup->name;
            $this->backupService->deleteBackup($backup);

            return response()->json([
                'success' => true,
                'message' => 'Backup "' . $backupName . '" deleted successfully.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get estimated backup size
     */
    public function estimateSize(Request $request)
    {
        $request->validate([
            'modules' => 'required|array|min:1',
            'modules.*' => 'in:patient_records,prenatal_monitoring,child_records,immunization_records,vaccine_management'
        ]);

        $estimatedSize = $this->backupService->getEstimatedSize($request->modules);

        return response()->json([
            'estimated_size' => number_format($estimatedSize, 1) . ' MB (uncompressed)'
        ]);
    }

    /**
     * Generate backup name
     */
    private function generateBackupName(array $modules): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $allModules = ['patient_records', 'prenatal_monitoring', 'child_records', 'immunization_records', 'vaccine_management'];

        if (count($modules) === count($allModules)) {
            return "Full_Backup_{$timestamp}";
        } else {
            return "Selective_Backup_{$timestamp}";
        }
    }

    /**
     * Calculate backup progress percentage
     */
    private function calculateProgress(CloudBackup $backup): int
    {
        return match ($backup->status) {
            'pending' => 0,
            'in_progress' => 50,
            'completed' => 100,
            'failed' => 0,
            default => 0
        };
    }

    /**
     * Get status message for backup
     */
    private function getStatusMessage(CloudBackup $backup): string
    {
        return match ($backup->status) {
            'pending' => 'Backup is queued for processing...',
            'in_progress' => 'Backup in progress...',
            'completed' => 'Backup completed successfully!',
            'failed' => 'Backup failed: ' . ($backup->error_message ?: 'Unknown error'),
            default => 'Unknown status'
        };
    }
}