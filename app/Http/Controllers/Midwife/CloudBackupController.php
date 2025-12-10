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

            // OPTIMIZED: Batch lookup to avoid N+1 queries
            $existingFileIds = CloudBackup::whereIn('google_drive_file_id', collect($driveFiles)->pluck('id'))
                ->pluck('google_drive_file_id')
                ->toArray();

            foreach ($driveFiles as $driveFile) {
                // Check if backup already exists in database using batch-loaded data
                if (!in_array($driveFile['id'], $existingFileIds)) {
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
                'status' => RestoreOperation::STATUS_PENDING,
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
     * Get restore progress for real-time updates
     */
    public function restoreProgress($id)
    {
        try {
            $restoreOperation = RestoreOperation::findOrFail($id);

            // Refresh model to ensure we have latest data, especially error states
            $restoreOperation->refresh();

            // If restore is pending, mark it as queued and dispatch the job once
            if ($restoreOperation->status === RestoreOperation::STATUS_PENDING) {
                $restoreOperation->update([
                    'status' => RestoreOperation::STATUS_IN_PROGRESS,
                    'current_step' => 'Queueing restore job...',
                    'progress' => max($restoreOperation->progress ?? 0, 5)
                ]);

                // Process restore directly without blocking the response cycle
                $this->startRestoreProcessing($restoreOperation->fresh());
                $restoreOperation = $restoreOperation->fresh();
            }

            // If the underlying job has failed, ensure status reflects that and return detailed JSON
            if ($restoreOperation->status === RestoreOperation::STATUS_IN_PROGRESS && $restoreOperation->error_message) {
                $restoreOperation->update([
                    'status' => RestoreOperation::STATUS_FAILED,
                    'current_step' => $restoreOperation->current_step ?? 'Restore failed',
                    'progress' => $restoreOperation->progress ?? 0
                ]);
                $restoreOperation = $restoreOperation->fresh();
            }

            $response = [
                'status' => $restoreOperation->status,
                'progress' => $restoreOperation->progress ?? 0,
                'current_step' => $restoreOperation->current_step ?? 'Initializing...',
                'error' => $restoreOperation->error_message
            ];

            // Add success message when completed
            if ($restoreOperation->status === RestoreOperation::STATUS_COMPLETED) {
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
            \Log::error('Restore progress error: ' . $e->getMessage(), [
                'restore_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'failed',
                'progress' => 0,
                'current_step' => 'Error',
                'error' => 'Failed to get restore progress: ' . $e->getMessage(),
                'message' => 'Restore progress check failed.'
            ], 500);
        }
    }

    /**
     * Start processing the restore operation
     * This dispatches a job to process the restore asynchronously
     */
    private function startRestoreProcessing(RestoreOperation $restoreOperation)
    {
        // Dispatch the restore job
        \App\Jobs\ProcessRestoreJob::dispatch($restoreOperation->id);

        \Log::info('Restore job dispatched', [
            'restore_id' => $restoreOperation->id,
            'backup_id' => $restoreOperation->backup_id
        ]);
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