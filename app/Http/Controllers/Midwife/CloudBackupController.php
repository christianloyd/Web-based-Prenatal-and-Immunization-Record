<?php

namespace App\Http\Controllers\Midwife;

use App\Http\Controllers\Controller;
use App\Models\CloudBackup;
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
     * Get backup data for AJAX requests
     */
    public function getData(Request $request)
    {
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
                'name' => $backup->name,
                'type' => $backup->type,
                'format' => $backup->format,
                'modules' => $backup->modules,
                'formatted_modules' => $backup->formatted_modules,
                'size' => $backup->file_size ?: '0 MB',
                'status' => $backup->status,
                'created_at' => $backup->created_at->toISOString(),
                'storage_location' => $backup->storage_location,
                'encrypted' => $backup->encrypted,
                'compressed' => $backup->compressed,
                'error' => $backup->error_message,
                'status_badge' => $backup->status_badge,
                'google_drive_file_id' => $backup->google_drive_file_id,
                'google_drive_link' => $backup->google_drive_link
            ];
        });

        return response()->json([
            'backups' => $backups,
            'stats' => $this->backupService->getBackupStats()
        ]);
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
     * Restore from a backup
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_id' => 'required|exists:cloud_backups,id',
            'restore_options' => 'nullable|array',
            'restore_options.*' => 'in:create_backup,verify_integrity,selective_restore',
            'confirm_restore' => 'required|accepted'
        ]);

        try {
            $backup = CloudBackup::findOrFail($request->backup_id);

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

                // Refresh the original backup model to ensure we have the latest status
                $backup->refresh();
            }

            // Double-check the backup status before restore (in case it was affected by pre-backup process)
            if ($backup->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot restore from incomplete backup'
                ], 400);
            }

            // Perform the restore
            $this->backupService->restoreBackup($backup);

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
            $backup = CloudBackup::findOrFail($id);
            
            $this->backupService->deleteBackup($backup);

            return response()->json([
                'success' => true,
                'message' => 'Backup "' . $backup->name . '" deleted successfully.'
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