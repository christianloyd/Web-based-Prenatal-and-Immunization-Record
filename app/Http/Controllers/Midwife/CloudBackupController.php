<?php

namespace App\Http\Controllers\Midwife;

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
     * Display the cloud backup management page
     */
    public function index()
    {
        $stats = $this->backupService->getBackupStats();
        $googleDriveConnected = $this->backupService->testGoogleDriveConnection();
        $driveStorage = $this->backupService->getGoogleDriveStorageInfo();
        $moduleInfo = $this->backupService->getModuleInfo();

        // Check if using OAuth2 and if authenticated
        $googleDriveService = app(\App\Services\GoogleDriveService::class);
        $isOAuth = file_exists(storage_path('app/google/oauth_credentials.json'));
        $isAuthenticated = $googleDriveService ? $googleDriveService->isAuthenticated() : false;

        return view('midwife.cloudbackup.index', compact(
            'stats',
            'googleDriveConnected',
            'driveStorage',
            'moduleInfo',
            'isOAuth',
            'isAuthenticated'
        ));
    }

    /**
     * Sync Google Drive backups with database
     */
    public function syncGoogleDrive()
    {
        try {
            $googleDriveService = app(\App\Services\GoogleDriveService::class);
            if (!$googleDriveService || !$googleDriveService->isAuthenticated()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Drive not authenticated'
                ]);
            }

            // Get all files from Google Drive backup folder
            $driveFiles = $googleDriveService->listBackupFiles();
            $syncedCount = 0;

            foreach ($driveFiles as $driveFile) {
                // Check if backup already exists in database
                $existingBackup = CloudBackup::where('google_drive_file_id', $driveFile['id'])->first();

                if (!$existingBackup) {
                    // Create database entry for Google Drive backup
                    CloudBackup::create([
                        'name' => $driveFile['name'],
                        'type' => str_contains($driveFile['name'], 'Full') ? 'full' : 'selective',
                        'format' => 'sql_dump',
                        'modules' => ['patient_records', 'prenatal_monitoring', 'child_records', 'immunization_records', 'vaccine_management'],
                        'file_size' => $this->formatBytes($driveFile['size']),
                        'status' => 'completed',
                        'storage_location' => 'google_drive',
                        'google_drive_file_id' => $driveFile['id'],
                        'google_drive_link' => $driveFile['web_view_link'] ?? null,
                        'verified' => true,
                        'created_by' => Auth::id(),
                        'started_at' => $driveFile['created_time'],
                        'completed_at' => $driveFile['created_time']
                    ]);
                    $syncedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Synced {$syncedCount} backups from Google Drive",
                'synced_count' => $syncedCount
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync Google Drive: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes)
    {
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
     * Get backup data for AJAX requests
     */
    public function getData(Request $request)
    {
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
                    'verified' => $backup->verified ?? false,
                    'error' => $backup->error_message,
                    'status_badge' => $backup->status_badge ?? '',
                    'google_drive_file_id' => $backup->google_drive_file_id,
                    'google_drive_link' => $backup->google_drive_link,
                    'file_path' => $backup->file_path,
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
     * Restore from a backup with progress tracking
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

            // Debug: Log what restore options we received
            \Log::info('Restore request received', [
                'backup_id' => $request->backup_id,
                'restore_options' => $request->restore_options,
                'backup_name' => $backup->name
            ]);

            if ($backup->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot restore from incomplete backup'
                ], 400);
            }

            // Create restore operation record IMMEDIATELY to track progress
            $restoreOperation = RestoreOperation::create([
                'backup_id' => $backup->id,
                'backup_name' => $backup->name,
                'modules_restored' => $backup->modules,
                'status' => 'pending',
                'progress' => 0,
                'current_step' => 'Initializing restore...',
                'restore_options' => $request->restore_options ?? [],
                'started_at' => now(),
                'restored_by' => Auth::id()
            ]);

            // Return restore operation ID immediately so UI can start tracking progress
            return response()->json([
                'success' => true,
                'restore_id' => $restoreOperation->id,
                'message' => 'Restore initiated. Tracking progress...'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start restore: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process the restore operation asynchronously
     * This should be called automatically after restore() returns
     */
    public function processRestore($restoreId)
    {
        $restoreOperation = RestoreOperation::find($restoreId);
        if (!$restoreOperation) {
            return;
        }

        try {
            $backup = CloudBackup::findOrFail($restoreOperation->backup_id);

            // Update status to in_progress
            $restoreOperation->update([
                'status' => 'in_progress',
                'progress' => 10,
                'current_step' => 'Starting restore process...'
            ]);

            // Create backup before restore if requested
            if (in_array('create_backup', $restoreOperation->restore_options ?? [])) {
                $restoreOperation->update([
                    'progress' => 20,
                    'current_step' => 'Creating pre-restore backup...'
                ]);

                \Log::info('Creating pre-restore backup...');
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
                    'created_by' => $restoreOperation->restored_by
                ]);

                $this->backupService->createBackup($preRestoreBackup);
                $backup->refresh();
            }

            // Verify backup integrity if requested
            if (in_array('verify_integrity', $restoreOperation->restore_options ?? [])) {
                $restoreOperation->update([
                    'progress' => 40,
                    'current_step' => 'Verifying backup integrity...'
                ]);

                $integrityCheck = $this->backupService->verifyBackupIntegrity($backup);

                if (!$integrityCheck['valid']) {
                    throw new Exception('Backup integrity verification failed: ' . $integrityCheck['error']);
                }
            }

            // Perform the restore
            $restoreOperation->update([
                'progress' => 60,
                'current_step' => 'Restoring database...'
            ]);

            $this->backupService->restoreBackup($backup);

            // Mark as completed
            $restoreOperation->update([
                'status' => 'completed',
                'progress' => 100,
                'current_step' => 'Restore completed successfully!',
                'completed_at' => now(),
                'restored_at' => now()
            ]);

        } catch (Exception $e) {
            $restoreOperation->update([
                'status' => 'failed',
                'progress' => 0,
                'current_step' => 'Restore failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now()
            ]);

            \Log::error('Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Get restore progress for real-time updates
     */
    public function restoreProgress($id)
    {
        try {
            $restoreOperation = RestoreOperation::findOrFail($id);

            // If restore is pending, start processing it
            if ($restoreOperation->status === 'pending') {
                // Process restore asynchronously
                dispatch(function () use ($id) {
                    $this->processRestore($id);
                })->afterResponse();
            }

            $response = [
                'status' => $restoreOperation->status,
                'progress' => $restoreOperation->progress ?? 0,
                'current_step' => $restoreOperation->current_step ?? 'Initializing...',
                'error' => $restoreOperation->error_message
            ];

            // Add success message when completed
            if ($restoreOperation->status === 'completed') {
                $backup = $restoreOperation->backup;
                $restoreMessage = 'Data restored successfully from "' . $restoreOperation->backup_name . '"!';
                if ($backup && $backup->type === 'selective') {
                    $modules = is_array($backup->modules) ? $backup->modules : [];
                    $moduleNames = array_map(function($module) {
                        return str_replace('_', ' ', ucwords($module, '_'));
                    }, $modules);
                    $restoreMessage .= ' Only the following modules were restored: ' . implode(', ', $moduleNames) . '. Other data was preserved.';
                }
                $response['message'] = $restoreMessage;
            }

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'progress' => 0,
                'current_step' => 'Error',
                'error' => 'Failed to get restore progress: ' . $e->getMessage()
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