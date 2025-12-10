<?php

namespace App\Jobs;

use App\Models\CloudBackup;
use App\Models\RestoreOperation;
use App\Services\DatabaseBackupService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessRestoreJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected int $restoreOperationId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $restoreOperationId)
    {
        $this->restoreOperationId = $restoreOperationId;
    }

    /**
     * Execute the job.
     */
    public function handle(DatabaseBackupService $backupService): void
    {
        $restoreOperation = RestoreOperation::find($this->restoreOperationId);
        if (!$restoreOperation) {
            Log::error('Restore operation not found', ['restore_id' => $this->restoreOperationId]);
            return;
        }

        try {
            $backup = CloudBackup::findOrFail($restoreOperation->backup_id);

            // Update status to in_progress
            $restoreOperation->update([
                'status' => RestoreOperation::STATUS_IN_PROGRESS,
                'progress' => 10,
                'current_step' => 'Starting restore process...'
            ]);

            // Create backup before restore if requested
            if (in_array('create_backup', $restoreOperation->restore_options ?? [])) {
                $restoreOperation->update([
                    'progress' => 20,
                    'current_step' => 'Creating pre-restore backup...'
                ]);

                Log::info('Creating pre-restore backup...');
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

                $backupService->createBackup($preRestoreBackup);
                $backup->refresh();
            }

            // Verify backup integrity if requested
            if (in_array('verify_integrity', $restoreOperation->restore_options ?? [])) {
                $restoreOperation->update([
                    'progress' => 40,
                    'current_step' => 'Verifying backup integrity...'
                ]);

                $integrityCheck = $backupService->verifyBackupIntegrity($backup);

                if (!$integrityCheck['valid']) {
                    throw new Exception('Backup integrity verification failed: ' . $integrityCheck['error']);
                }
            }

            // Perform the restore
            $restoreOperation->update([
                'progress' => 60,
                'current_step' => 'Restoring database...'
            ]);

            $backupService->restoreBackup($backup);

            // Mark as completed
            $restoreOperation->update([
                'status' => RestoreOperation::STATUS_COMPLETED,
                'progress' => 100,
                'current_step' => 'Restore completed successfully!',
                'completed_at' => now(),
                'restored_at' => now()
            ]);

        } catch (Exception $e) {
            $restoreOperation->update([
                'status' => RestoreOperation::STATUS_FAILED,
                'progress' => 0,
                'current_step' => 'Restore failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now()
            ]);

            Log::error('Restore failed: ' . $e->getMessage(), [
                'restore_id' => $this->restoreOperationId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        $restoreOperation = RestoreOperation::find($this->restoreOperationId);
        if ($restoreOperation) {
            $restoreOperation->update([
                'status' => RestoreOperation::STATUS_FAILED,
                'progress' => 0,
                'current_step' => 'Restore job failed',
                'error_message' => 'Job failed: ' . $exception->getMessage(),
                'completed_at' => now()
            ]);
        }

        Log::error('ProcessRestoreJob failed', [
            'restore_id' => $this->restoreOperationId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
