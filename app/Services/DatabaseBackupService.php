<?php

namespace App\Services;

use App\Models\CloudBackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;
use Carbon\Carbon;
use Exception;

class DatabaseBackupService
{
    private ?GoogleDriveService $googleDrive;

    public function __construct(GoogleDriveService $googleDrive = null)
    {
        $this->googleDrive = $googleDrive;
    }

    private $moduleConfig = [
        'patient_records' => [
            'tables' => ['patients', 'patient_profiles'],
            'size_estimate' => 45.2
        ],
        'prenatal_monitoring' => [
            'tables' => ['prenatal_records', 'prenatal_checkups', 'high_risk_cases'],
            'size_estimate' => 78.5
        ],
        'child_records' => [
            'tables' => ['child_records', 'growth_tracking'],
            'size_estimate' => 32.1
        ],
        'immunization_records' => [
            'tables' => ['immunizations', 'immunization_records'],
            'size_estimate' => 28.7
        ],
        'vaccine_management' => [
            'tables' => ['vaccines', 'vaccine_inventory', 'vaccine_transactions'],
            'size_estimate' => 12.3
        ]
    ];

    /**
     * Create a new backup
     */
    public function createBackup(CloudBackup $backup): void
    {
        try {
            $backup->update([
                'status' => 'in_progress',
                'started_at' => now()
            ]);

            // Generate backup file
            $fileName = $this->generateFileName($backup);
            $filePath = $this->createSQLDump($backup, $fileName);
            
            // Calculate actual file size
            $fileSize = $this->getFileSize($filePath);
            
            // Upload to Google Drive if available
            $driveResult = null;
            if ($this->googleDrive) {
                try {
                    \Log::info("Attempting to upload backup to Google Drive: " . $fileName);
                    $driveResult = $this->uploadToGoogleDrive($filePath, $fileName, [
                        'backup_id' => $backup->id,
                        'backup_type' => $backup->type,
                        'modules' => $backup->modules,
                        'created_by' => $backup->created_by
                    ]);
                    \Log::info("Google Drive upload successful: " . json_encode($driveResult));
                } catch (Exception $e) {
                    // Log detailed error information
                    \Log::error("Google Drive upload failed for backup {$backup->id}: " . $e->getMessage());
                    \Log::error("Exception trace: " . $e->getTraceAsString());
                }
            } else {
                \Log::warning("Google Drive service not available for backup {$backup->id}");
            }
            
            $backup->update([
                'status' => 'completed',
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'completed_at' => now(),
                'verified' => true,
                'google_drive_file_id' => $driveResult['file_id'] ?? null,
                'google_drive_link' => $driveResult['web_view_link'] ?? null,
                'storage_location' => $driveResult ? 'google_drive' : 'local'
            ]);

        } catch (Exception $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now()
            ]);
            
            throw $e;
        }
    }

    /**
     * Generate backup file name
     */
    private function generateFileName(CloudBackup $backup): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $prefix = $backup->type === 'full' ? 'full_backup' : 'selective_backup';
        
        return "{$prefix}_{$timestamp}.sql";
    }

    /**
     * Create SQL dump file (Production-safe version)
     */
    private function createSQLDump(CloudBackup $backup, string $fileName): string
    {
        $dumpPath = storage_path("app/backups/{$fileName}");
        
        // Ensure backup directory exists
        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $sqlContent = "-- Healthcare Database Backup\n";
        $sqlContent .= "-- Created: " . now()->toDateTimeString() . "\n";
        $sqlContent .= "-- Type: " . ucfirst($backup->type) . " Backup\n\n";
        $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        if ($backup->type === 'full') {
            // Get all tables in database
            $tables = DB::select("SHOW TABLES");
            $tableColumn = 'Tables_in_' . config('database.connections.mysql.database');
            
            foreach ($tables as $table) {
                $tableName = $table->$tableColumn;
                $sqlContent .= $this->exportTable($tableName);
            }
        } else {
            // Selective backup - only selected modules
            $tables = $this->getTablesToBackup($backup->modules);
            
            foreach ($tables as $tableName) {
                if ($this->tableExists($tableName)) {
                    $sqlContent .= $this->exportTable($tableName);
                }
            }
        }

        $sqlContent .= "\nSET FOREIGN_KEY_CHECKS=1;\n";
        $sqlContent .= "-- Backup completed: " . now()->toDateTimeString() . "\n";

        // Write to file
        file_put_contents($dumpPath, $sqlContent);

        return $dumpPath;
    }

    /**
     * Export a single table to SQL
     */
    private function exportTable(string $tableName): string
    {
        $sql = "\n-- Table: {$tableName}\n";
        $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
        
        // Get table structure
        $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
        $sql .= $createTable->{'Create Table'} . ";\n\n";
        
        // Get table data
        $rows = DB::table($tableName)->get();
        
        if ($rows->count() > 0) {
            $sql .= "INSERT INTO `{$tableName}` VALUES\n";
            $insertValues = [];
            
            foreach ($rows as $row) {
                $values = array_map(function($value) {
                    return $value === null ? 'NULL' : "'" . addslashes($value) . "'";
                }, (array) $row);
                $insertValues[] = '(' . implode(', ', $values) . ')';
            }
            
            $sql .= implode(",\n", $insertValues) . ";\n\n";
        }
        
        return $sql;
    }

    /**
     * Check if table exists
     */
    private function tableExists(string $tableName): bool
    {
        try {
            return DB::table($tableName)->exists();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get tables to backup based on selected modules
     */
    private function getTablesToBackup(array $modules): array
    {
        $tables = [];
        
        foreach ($modules as $module) {
            if (isset($this->moduleConfig[$module])) {
                $tables = array_merge($tables, $this->moduleConfig[$module]['tables']);
            }
        }

        return array_unique($tables);
    }

    /**
     * Get file size in human readable format
     */
    private function getFileSize(string $filePath): string
    {
        $bytes = filesize($filePath);
        
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
     * Upload file to Google Drive
     */
    private function uploadToGoogleDrive(string $filePath, string $fileName, array $metadata = []): array
    {
        if (!$this->googleDrive) {
            throw new Exception("Google Drive service not available");
        }
        
        try {
            return $this->googleDrive->uploadFile($filePath, $fileName, $metadata);
        } catch (Exception $e) {
            throw new Exception("Failed to upload to Google Drive: " . $e->getMessage());
        }
    }

    /**
     * Calculate estimated backup size
     */
    public function getEstimatedSize(array $modules): float
    {
        $totalSize = 0;
        
        foreach ($modules as $module) {
            if (isset($this->moduleConfig[$module])) {
                $totalSize += $this->moduleConfig[$module]['size_estimate'];
            }
        }

        return $totalSize;
    }

    /**
     * Get backup statistics
     */
    public function getBackupStats(): array
    {
        $totalBackups = CloudBackup::count();
        $successfulBackups = CloudBackup::where('status', 'completed')->count();
        $lastBackup = CloudBackup::latest()->first();
        
        // Calculate total storage used
        $storageUsed = CloudBackup::where('status', 'completed')
            ->get()
            ->sum(function ($backup) {
                $size = $backup->file_size;
                if (str_contains($size, 'GB')) {
                    return (float) str_replace(' GB', '', $size) * 1024;
                } elseif (str_contains($size, 'MB')) {
                    return (float) str_replace(' MB', '', $size);
                } elseif (str_contains($size, 'KB')) {
                    return (float) str_replace(' KB', '', $size) / 1024;
                }
                return 0;
            });

        return [
            'total_backups' => $totalBackups,
            'successful_backups' => $successfulBackups,
            'last_backup' => $lastBackup ? $lastBackup->created_at->diffForHumans() : 'Never',
            'storage_used' => $storageUsed > 1024 
                ? number_format($storageUsed / 1024, 1) . ' GB' 
                : ($storageUsed < 1 
                    ? number_format($storageUsed * 1024, 1) . ' KB'
                    : number_format($storageUsed, 1) . ' MB')
        ];
    }

    /**
     * Restore database from backup (Production-safe version)
     */
    public function restoreBackup(CloudBackup $backup): void
    {
        if ($backup->status !== 'completed') {
            throw new Exception('Cannot restore from incomplete backup');
        }

        if (!file_exists($backup->file_path)) {
            throw new Exception('Backup file not found');
        }

        $sqlContent = file_get_contents($backup->file_path);
        
        if (empty($sqlContent)) {
            throw new Exception('Backup file is empty or corrupted');
        }

        // Split SQL content into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sqlContent)),
            function($statement) {
                return !empty($statement) && 
                       !str_starts_with($statement, '--') && 
                       !str_starts_with($statement, '/*');
            }
        );

        DB::beginTransaction();
        
        try {
            // Tables that should not be restored (system tables)
            $systemTables = ['cache', 'sessions', 'personal_access_tokens', 'password_reset_tokens', 'failed_jobs'];
            $clearedTables = []; // Track which tables have been cleared
            
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    // Skip system table operations
                    $shouldSkip = false;
                    foreach ($systemTables as $systemTable) {
                        if (str_contains(strtoupper($statement), 'CREATE TABLE `' . strtoupper($systemTable) . '`') ||
                            str_contains(strtoupper($statement), "CREATE TABLE `" . strtoupper($systemTable) . "`") ||
                            str_contains(strtoupper($statement), 'INSERT INTO `' . strtoupper($systemTable) . '`')) {
                            $shouldSkip = true;
                            break;
                        }
                    }
                    
                    if (!$shouldSkip) {
                        // Handle different statement types
                        if (str_contains(strtoupper($statement), 'CREATE TABLE')) {
                            // For CREATE TABLE statements, skip if table already exists
                            preg_match('/CREATE TABLE `([^`]+)`/', $statement, $matches);
                            if (!empty($matches[1])) {
                                $tableName = $matches[1];
                                // Check if table exists
                                if (!Schema::hasTable($tableName)) {
                                    DB::unprepared($statement);
                                }
                                // If table exists, skip creation but continue to data insertion
                            }
                        } elseif (str_contains(strtoupper($statement), 'INSERT INTO')) {
                            // For INSERT statements, clear existing data first (only once per table)
                            preg_match('/INSERT INTO `([^`]+)`/', $statement, $matches);
                            if (!empty($matches[1])) {
                                $tableName = $matches[1];
                                // Clear existing data for healthcare tables only (and only once)
                                $healthcareTables = ['patients', 'prenatal_records', 'child_records', 'immunizations', 'vaccines', 'prenatal_checkups', 'users'];
                                if (in_array($tableName, $healthcareTables) && !in_array($tableName, $clearedTables)) {
                                    DB::table($tableName)->truncate();
                                    $clearedTables[] = $tableName;
                                }
                            }
                            DB::unprepared($statement);
                        } else {
                            // Execute other statements (ALTER, etc.)
                            DB::unprepared($statement);
                        }
                    }
                }
            }
            
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Restore failed: " . $e->getMessage());
        }
    }

    /**
     * Delete backup file
     */
    public function deleteBackup(CloudBackup $backup): void
    {
        // Delete local file
        if ($backup->file_path && file_exists($backup->file_path)) {
            unlink($backup->file_path);
        }
        
        // Delete from Google Drive
        if ($backup->google_drive_file_id && $this->googleDrive) {
            try {
                $this->googleDrive->deleteFile($backup->google_drive_file_id);
            } catch (Exception $e) {
                \Log::warning("Failed to delete file from Google Drive: " . $e->getMessage());
            }
        }
        
        $backup->delete();
    }

    /**
     * Download backup file
     */
    public function downloadBackup(CloudBackup $backup)
    {
        // Try to download from local file first
        if ($backup->file_path && file_exists($backup->file_path)) {
            return response()->download($backup->file_path, basename($backup->file_path));
        }
        
        // If local file doesn't exist, download from Google Drive
        if ($backup->google_drive_file_id && $this->googleDrive) {
            $tempPath = storage_path("app/temp/" . basename($backup->file_path));
            
            // Ensure temp directory exists
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }
            
            if ($this->googleDrive->downloadFile($backup->google_drive_file_id, $tempPath)) {
                return response()->download($tempPath, basename($backup->file_path))->deleteFileAfterSend(true);
            }
        }
        
        throw new Exception('Backup file not found locally or on Google Drive');
    }

    /**
     * Test Google Drive connection
     */
    public function testGoogleDriveConnection(): bool
    {
        if (!$this->googleDrive) {
            return false;
        }
        
        try {
            return $this->googleDrive->testConnection();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get Google Drive storage information
     */
    public function getGoogleDriveStorageInfo(): array
    {
        if (!$this->googleDrive) {
            return [
                'limit' => 0,
                'usage' => 0,
                'available' => 0
            ];
        }
        
        try {
            return $this->googleDrive->getStorageQuota();
        } catch (Exception $e) {
            return [
                'limit' => 0,
                'usage' => 0,
                'available' => 0
            ];
        }
    }
}