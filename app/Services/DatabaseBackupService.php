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
            'tables' => ['patients', 'users']
        ],
        'prenatal_monitoring' => [
            'tables' => ['prenatal_records', 'prenatal_checkups']
        ],
        'child_records' => [
            'tables' => ['child_records']
        ],
        'immunization_records' => [
            'tables' => ['immunizations', 'child_immunizations']
        ],
        'vaccine_management' => [
            'tables' => ['vaccines', 'stock_transactions']
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

        // Use custom backup name if provided, otherwise generate name
        if (!empty($backup->name) && $backup->name !== 'Unnamed Backup') {
            // Clean the backup name for file system (remove invalid characters)
            $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $backup->name);
            $cleanName = trim($cleanName, '_');

            // If name becomes empty after cleaning, fallback to auto-generated
            if (empty($cleanName)) {
                $prefix = $backup->type === 'full' ? 'Full_Backup' : 'Selective_Backup';
                return "{$prefix}_{$timestamp}.sql";
            }

            return "{$cleanName}_{$timestamp}.sql";
        } else {
            // Auto-generate name for unnamed backups
            $prefix = $backup->type === 'full' ? 'Full_Backup' : 'Selective_Backup';
            return "{$prefix}_{$timestamp}.sql";
        }
    }

    /**
     * Create SQL dump file (Production-safe version)
     */
    private function createSQLDump(CloudBackup $backup, string $fileName): string
    {
        $backupDir = storage_path('app/backups');
        $dumpPath = $backupDir . '/' . $fileName;

        try {
            // Ensure backup directory exists with proper permissions
            if (!file_exists($backupDir)) {
                if (!mkdir($backupDir, 0755, true)) {
                    throw new Exception("Failed to create backup directory: {$backupDir}");
                }
            }

            // Check if directory is writable
            if (!is_writable($backupDir)) {
                throw new Exception("Backup directory is not writable: {$backupDir}");
            }

            // Test write permissions by creating and deleting a test file
            $testFile = $backupDir . '/test_' . time() . '.tmp';
            try {
                if (file_put_contents($testFile, 'test') === false) {
                    throw new Exception("Cannot write test file to backup directory: {$backupDir}");
                }
                if (file_exists($testFile)) {
                    unlink($testFile);
                }
            } catch (Exception $e) {
                throw new Exception("Backup directory write test failed: " . $e->getMessage());
            }

            // Test database connection
            try {
                DB::connection()->getPdo();
            } catch (Exception $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }

            \Log::info("Starting backup creation for: " . $fileName);

            $sqlContent = "-- Healthcare Database Backup\n";
            $sqlContent .= "-- Created: " . now()->toDateTimeString() . "\n";
            $sqlContent .= "-- Type: " . ucfirst($backup->type) . " Backup\n\n";
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            if ($backup->type === 'full') {
                // Get all tables in database
                try {
                    $tables = DB::select("SHOW TABLES");
                    $databaseName = config('database.connections.mysql.database');
                    $tableColumn = 'Tables_in_' . $databaseName;

                    if (empty($tables)) {
                        throw new Exception("No tables found in database: {$databaseName}");
                    }

                    \Log::info("Found " . count($tables) . " tables to backup");

                    foreach ($tables as $table) {
                        if (!isset($table->$tableColumn)) {
                            \Log::warning("Table column not found: {$tableColumn}");
                            continue;
                        }
                        $tableName = $table->$tableColumn;
                        \Log::info("Exporting table: {$tableName}");
                        $sqlContent .= $this->exportTable($tableName);
                    }
                } catch (Exception $e) {
                    throw new Exception("Failed to get database tables: " . $e->getMessage());
                }
            } else {
                // Selective backup - only selected modules
                $tables = $this->getTablesToBackup($backup->modules);

                if (empty($tables)) {
                    throw new Exception("No tables found for selected modules: " . implode(', ', $backup->modules));
                }

                \Log::info("Selective backup for tables: " . implode(', ', $tables));

                foreach ($tables as $tableName) {
                    if ($this->tableExists($tableName)) {
                        \Log::info("Exporting table: {$tableName}");
                        $sqlContent .= $this->exportTable($tableName);
                    } else {
                        \Log::warning("Table does not exist: {$tableName}");
                    }
                }
            }

            $sqlContent .= "\nSET FOREIGN_KEY_CHECKS=1;\n";
            $sqlContent .= "-- Backup completed: " . now()->toDateTimeString() . "\n";

            // Write to file with error handling
            $result = file_put_contents($dumpPath, $sqlContent);
            if ($result === false) {
                throw new Exception("Failed to write backup file: {$dumpPath}");
            }

            // Verify file was created and has content
            if (!file_exists($dumpPath)) {
                throw new Exception("Backup file was not created: {$dumpPath}");
            }

            $fileSize = filesize($dumpPath);
            if ($fileSize === 0) {
                throw new Exception("Backup file is empty: {$dumpPath}");
            }

            \Log::info("Backup file created successfully: {$dumpPath} ({$fileSize} bytes)");

            return $dumpPath;

        } catch (Exception $e) {
            // Clean up partial file if it exists
            if (file_exists($dumpPath)) {
                unlink($dumpPath);
            }
            throw new Exception("SQL dump creation failed: " . $e->getMessage());
        }
    }

    /**
     * Export a single table to SQL
     */
    private function exportTable(string $tableName): string
    {
        try {
            $sql = "\n-- Table: {$tableName}\n";
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";

            // Get table structure
            try {
                $createTableResult = DB::select("SHOW CREATE TABLE `{$tableName}`");
                if (empty($createTableResult)) {
                    throw new Exception("No CREATE TABLE result for table: {$tableName}");
                }
                $createTable = $createTableResult[0];
                $sql .= $createTable->{'Create Table'} . ";\n\n";
            } catch (Exception $e) {
                throw new Exception("Failed to get table structure for {$tableName}: " . $e->getMessage());
            }

            // Get table data
            try {
                $rows = DB::table($tableName)->get();
                \Log::info("Exporting {$rows->count()} rows from table: {$tableName}");

                if ($rows->count() > 0) {
                    // Get column names for proper INSERT statement
                    $firstRow = $rows->first();
                    if (!$firstRow) {
                        \Log::warning("No data found in table: {$tableName}");
                        return $sql;
                    }

                    $columns = array_keys((array) $firstRow);
                    if (empty($columns)) {
                        \Log::warning("No columns found in table: {$tableName}");
                        return $sql;
                    }

                    $columnsList = '`' . implode('`, `', $columns) . '`';
                    $sql .= "INSERT INTO `{$tableName}` ({$columnsList}) VALUES\n";
                    $insertValues = [];

                    foreach ($rows as $row) {
                        try {
                            $values = array_map(function($value) {
                                if ($value === null) {
                                    return 'NULL';
                                }
                                // Properly escape and quote values
                                $escaped = str_replace(
                                    ['\\', "'", "\r", "\n", "\t", "\0"],
                                    ['\\\\', "\\'", '\\r', '\\n', '\\t', '\\0'],
                                    (string) $value
                                );
                                return "'" . $escaped . "'";
                            }, array_values((array) $row));

                            // Ensure we have the right number of values
                            if (count($values) === count($columns)) {
                                $insertValues[] = '(' . implode(', ', $values) . ')';
                            } else {
                                \Log::warning("Column count mismatch in table {$tableName}: expected " . count($columns) . ", got " . count($values));
                            }
                        } catch (Exception $e) {
                            \Log::error("Error processing row in table {$tableName}: " . $e->getMessage());
                            continue;
                        }
                    }

                    // Only add INSERT if we have valid data
                    if (!empty($insertValues)) {
                        $sql .= implode(",\n", $insertValues) . ";\n\n";
                    } else {
                        \Log::warning("No valid data rows found in table: {$tableName}");
                    }
                } else {
                    \Log::info("Table {$tableName} is empty, skipping data export");
                }
            } catch (Exception $e) {
                \Log::error("Failed to get data from table {$tableName}: " . $e->getMessage());
                // Continue with table structure only
            }

            return $sql;

        } catch (Exception $e) {
            \Log::error("Failed to export table {$tableName}: " . $e->getMessage());
            // Return empty string rather than failing the entire backup
            return "\n-- ERROR: Failed to export table {$tableName}: " . $e->getMessage() . "\n\n";
        }
    }

    /**
     * Check if table exists
     */
    private function tableExists(string $tableName): bool
    {
        try {
            // Method 1: Use information_schema with proper quoting
            $databaseName = config('database.connections.mysql.database');
            $result = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.TABLES
                WHERE table_schema = ? AND table_name = ?
            ", [$databaseName, $tableName]);

            if (!empty($result) && $result[0]->count > 0) {
                return true;
            }

            // Method 2: Use SHOW TABLES with proper LIKE syntax
            try {
                $result = DB::select("SHOW TABLES LIKE ?", [$tableName]);
                return !empty($result);
            } catch (Exception $e2) {
                // Method 3: Try direct table query as final fallback
                try {
                    DB::table($tableName)->limit(1)->get();
                    return true;
                } catch (Exception $e3) {
                    return false;
                }
            }

        } catch (Exception $e) {
            \Log::warning("Error checking if table exists: {$tableName} - " . $e->getMessage());

            // Final fallback: try direct query
            try {
                DB::table($tableName)->limit(1)->get();
                return true;
            } catch (Exception $e3) {
                return false;
            }
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
     * Calculate estimated backup size based on actual database table sizes
     */
    public function getEstimatedSize(array $modules): float
    {
        $totalSizeBytes = 0;

        try {
            foreach ($modules as $module) {
                if (isset($this->moduleConfig[$module])) {
                    $tables = $this->moduleConfig[$module]['tables'];

                    foreach ($tables as $tableName) {
                        $sizeBytes = $this->getTableSize($tableName);
                        $totalSizeBytes += $sizeBytes;
                    }
                }
            }

            // Convert bytes to MB
            return $totalSizeBytes / (1024 * 1024);

        } catch (Exception $e) {
            \Log::error("Error calculating backup size: " . $e->getMessage());
            // Return a minimal size instead of failing
            return 0.1;
        }
    }

    /**
     * Get actual size of a database table
     */
    private function getTableSize(string $tableName): int
    {
        try {
            // Check if table exists
            if (!$this->tableExists($tableName)) {
                return 0;
            }

            $databaseName = config('database.connections.mysql.database');

            // Get table size information from MySQL
            $result = DB::select("
                SELECT
                    (data_length + index_length) as table_size
                FROM information_schema.TABLES
                WHERE table_schema = ? AND table_name = ?
            ", [$databaseName, $tableName]);

            if (empty($result)) {
                return 0;
            }

            return (int) $result[0]->table_size;

        } catch (Exception $e) {
            \Log::error("Error getting table size for {$tableName}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get real-time module information with actual record counts and sizes
     */
    public function getModuleInfo(): array
    {
        $moduleInfo = [];

        foreach ($this->moduleConfig as $moduleKey => $config) {
            $totalRecords = 0;
            $totalSize = 0;

            foreach ($config['tables'] as $tableName) {
                if ($this->tableExists($tableName)) {
                    try {
                        $recordCount = DB::table($tableName)->count();

                        // Only count size if table has actual records
                        // Empty tables still show allocated space in MySQL which is misleading
                        if ($recordCount > 0) {
                            $tableSize = $this->getTableSize($tableName);
                            $totalSize += $tableSize;
                        }

                        $totalRecords += $recordCount;
                    } catch (Exception $e) {
                        \Log::warning("Error getting info for table {$tableName}: " . $e->getMessage());
                    }
                }
            }

            $moduleInfo[$moduleKey] = [
                'record_count' => $totalRecords,
                'size_bytes' => $totalSize,
                'size_mb' => round($totalSize / (1024 * 1024), 3),
                'size_formatted' => $this->formatBytes($totalSize)
            ];
        }

        return $moduleInfo;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
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
     * Verify backup file integrity
     */
    public function verifyBackupIntegrity(CloudBackup $backup): array
    {
        $result = [
            'valid' => false,
            'checks' => [],
            'error' => null
        ];

        try {
            // Check 1: Backup status
            $result['checks']['status'] = $backup->status === 'completed';
            if (!$result['checks']['status']) {
                $result['error'] = 'Backup is not completed';
                return $result;
            }

            // Check 2: File exists and is accessible
            try {
                $sqlContent = $this->getBackupFileContent($backup);
                $result['checks']['file_accessible'] = !empty($sqlContent);

                if (empty($sqlContent)) {
                    $result['error'] = 'Backup file is empty or inaccessible';
                    return $result;
                }
            } catch (Exception $e) {
                $result['checks']['file_accessible'] = false;
                $result['error'] = 'Cannot access backup file: ' . $e->getMessage();
                return $result;
            }

            // Check 3: Basic SQL structure validation
            $result['checks']['sql_structure'] = $this->validateSqlStructure($sqlContent);
            if (!$result['checks']['sql_structure']) {
                $result['error'] = 'Invalid SQL structure in backup file';
                return $result;
            }

            // Check 4: Contains essential tables
            $result['checks']['essential_tables'] = $this->validateEssentialTables($sqlContent);
            if (!$result['checks']['essential_tables']) {
                $result['error'] = 'Backup missing essential database tables';
                return $result;
            }

            // Check 5: File size validation
            $actualSize = strlen($sqlContent);
            $minExpectedSize = 1024; // At least 1KB
            $result['checks']['file_size'] = $actualSize >= $minExpectedSize;

            if (!$result['checks']['file_size']) {
                $result['error'] = 'Backup file too small, possibly corrupted';
                return $result;
            }

            // All checks passed
            $result['valid'] = true;
            return $result;

        } catch (Exception $e) {
            $result['error'] = 'Integrity verification failed: ' . $e->getMessage();
            return $result;
        }
    }

    /**
     * Validate SQL file structure
     */
    private function validateSqlStructure(string $sqlContent): bool
    {
        // Check for basic SQL dump markers
        $hasCreateTable = strpos($sqlContent, 'CREATE TABLE') !== false;
        $hasInsertInto = strpos($sqlContent, 'INSERT INTO') !== false || strpos($sqlContent, 'CREATE TABLE') !== false;

        return $hasCreateTable || $hasInsertInto;
    }

    /**
     * Validate that backup contains some valid tables (not necessarily essential ones for selective backups)
     */
    private function validateEssentialTables(string $sqlContent): bool
    {
        // For selective backups, we just need to ensure there are SOME tables
        // Common healthcare tables that might be in any backup
        $possibleTables = [
            'users', 'cloud_backups', 'patients', 'prenatal_records', 'child_records',
            'immunization_records', 'vaccines', 'prenatal_checkups', 'immunizations'
        ];

        // Check if at least ONE table exists in the backup
        foreach ($possibleTables as $table) {
            if (strpos($sqlContent, "`{$table}`") !== false || strpos($sqlContent, $table) !== false) {
                return true; // Found at least one valid table
            }
        }

        return false; // No valid tables found
    }

    /**
     * Restore database from backup (Production-safe version)
     */
    public function restoreBackup(CloudBackup $backup): void
    {
        // Refresh the backup model to ensure we have the latest data from database
        $backup->refresh();

        if ($backup->status !== 'completed') {
            \Log::error("Restore attempted on backup with status: {$backup->status}", [
                'backup_id' => $backup->id,
                'backup_name' => $backup->name,
                'status' => $backup->status,
                'completed_at' => $backup->completed_at
            ]);
            throw new Exception('Cannot restore from incomplete backup. Current status: ' . $backup->status);
        }

        \Log::info("Starting restore from backup: {$backup->name} (ID: {$backup->id})", [
            'backup_status' => $backup->status,
            'backup_size' => $backup->file_size,
            'storage_location' => $backup->storage_location
        ]);

        // Try to get the backup file content (local or from cloud)
        $sqlContent = $this->getBackupFileContent($backup);

        if (empty($sqlContent)) {
            throw new Exception('Backup file is empty or corrupted');
        }

        // Split SQL content into individual statements more carefully
        $statements = $this->splitSqlStatements($sqlContent);

        // Tables that should not be restored (system tables)
        $systemTables = ['cache', 'sessions', 'personal_access_tokens', 'password_reset_tokens', 'failed_jobs'];
        $clearedTables = []; // Track which tables have been cleared

        try {
            \Log::info("Starting database restore process");

            // Phase 1: Disable foreign key checks and handle DDL (without transactions)
            DB::unprepared('SET FOREIGN_KEY_CHECKS = 0');
            DB::unprepared('SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"');
            DB::unprepared('SET AUTOCOMMIT = 0');

            // Separate DDL and DML statements
            $ddlStatements = [];
            $dmlStatements = [];

            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement)) {
                    continue;
                }

                $upperStatement = strtoupper($statement);

                // Skip system table operations
                $shouldSkip = false;
                foreach ($systemTables as $systemTable) {
                    $upperSystemTable = strtoupper($systemTable);
                    if (str_contains($upperStatement, 'CREATE TABLE `' . $upperSystemTable . '`') ||
                        str_contains($upperStatement, 'INSERT INTO `' . $upperSystemTable . '`') ||
                        str_contains($upperStatement, 'DROP TABLE IF EXISTS `' . $upperSystemTable . '`')) {
                        $shouldSkip = true;
                        break;
                    }
                }

                if ($shouldSkip) {
                    continue;
                }

                // Categorize statements
                if (str_contains($upperStatement, 'DROP TABLE IF EXISTS') ||
                    str_contains($upperStatement, 'CREATE TABLE') ||
                    str_contains($upperStatement, 'ALTER TABLE') ||
                    str_contains($upperStatement, 'SET FOREIGN_KEY_CHECKS')) {
                    $ddlStatements[] = $statement;
                } elseif (str_contains($upperStatement, 'INSERT INTO')) {
                    $dmlStatements[] = $statement;
                }
            }

            // Execute DDL statements first (no transaction needed)
            \Log::info("Executing DDL statements (" . count($ddlStatements) . ")");
            foreach ($ddlStatements as $statement) {
                try {
                    DB::unprepared($statement);
                } catch (Exception $e) {
                    \Log::error("DDL Error: " . $e->getMessage() . " - Statement: " . substr($statement, 0, 200));
                    throw $e;
                }
            }

            // Phase 2: Always start a transaction when autocommit is disabled
            \Log::info("Starting transaction for data operations");
            DB::beginTransaction();
            
            try {
                // Phase 2a: Clear existing data if DML statements exist
                if (!empty($dmlStatements)) {
                    // Determine which tables are being restored from the backup
                    $tablesToRestore = $this->getTablesFromInsertStatements($dmlStatements);

                    // For selective backups, only clear tables that are being restored
                    // For full backups, clear all tables as before
                    if ($backup->type === 'selective') {
                        \Log::info("Selective restore: Only clearing tables being restored: " . implode(', ', $tablesToRestore));
                        $tablesToClear = $this->orderTablesForClearing($tablesToRestore);
                    } else {
                        // Full backup: clear all tables in dependency order
                        $tablesToClear = [
                            // Child tables with foreign keys first
                            'stock_transactions',
                            'child_immunizations',
                            'immunizations',
                            'prenatal_checkups',
                            'prenatal_records',
                            'child_records',
                            'cloud_backups', // References users
                            'notifications', // References users
                            // Then parent tables
                            'patients',
                            'vaccines',
                            'users',
                        ];
                        \Log::info("Full restore: Clearing all tables");
                    }

                    foreach ($tablesToClear as $tableName) {
                        if ($this->tableExists($tableName)) {
                            try {
                                $count = DB::table($tableName)->count();
                                if ($count > 0) {
                                    \Log::info("Clearing table: {$tableName} ({$count} records)");
                                    DB::table($tableName)->delete();
                                }
                            } catch (Exception $e) {
                                \Log::warning("Warning: Could not clear table {$tableName}: " . $e->getMessage());
                                // Continue clearing other tables even if one fails
                            }
                        }
                    }

                    // Execute INSERT statements in proper order for foreign key constraints
                    $orderedStatements = $this->orderInsertStatements($dmlStatements);
                    \Log::info("Executing DML statements (" . count($orderedStatements) . ") in dependency order");
                    
                    foreach ($orderedStatements as $statement) {
                        if ($this->isValidInsertStatement($statement)) {
                            try {
                                DB::unprepared($statement);
                            } catch (Exception $e) {
                                \Log::error("DML Error: " . $e->getMessage() . " - Statement: " . substr($statement, 0, 200));
                                throw $e;
                            }
                        } else {
                            \Log::warning("Skipping invalid INSERT statement: " . substr($statement, 0, 100));
                        }
                    }
                } else {
                    \Log::info("No DML statements to execute, only DDL changes applied");
                }

                // Commit the transaction
                DB::commit();
                \Log::info("Transaction committed successfully");

            } catch (Exception $e) {
                DB::rollBack();
                \Log::error("Transaction rolled back due to error: " . $e->getMessage());
                throw $e;
            }

            // Phase 3: Re-enable foreign key checks
            DB::unprepared('SET FOREIGN_KEY_CHECKS = 1');
            DB::unprepared('SET AUTOCOMMIT = 1');

            \Log::info("Database restore completed successfully");

        } catch (Exception $e) {
            // Cleanup: Rollback any active transaction and re-enable foreign key checks
            try {
                // Check if we're in a transaction and roll it back if needed
                if (DB::transactionLevel() > 0) {
                    \Log::info("Rolling back active transaction during cleanup");
                    DB::rollBack();
                }
                
                // Always restore MySQL settings regardless of transaction state
                DB::unprepared('SET FOREIGN_KEY_CHECKS = 1');
                DB::unprepared('SET AUTOCOMMIT = 1');
                \Log::info("Database settings restored during error cleanup");
            } catch (Exception $cleanupException) {
                \Log::error("Cleanup error: " . $cleanupException->getMessage());
                // Continue with original error reporting even if cleanup fails
            }

            \Log::error("Restore failed: " . $e->getMessage());
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

    /**
     * Get backup file content from local storage or Google Drive
     */
    private function getBackupFileContent(CloudBackup $backup): string
    {
        // Try local file first
        if ($backup->file_path && file_exists($backup->file_path)) {
            \Log::info("Reading backup from local file: {$backup->file_path}");
            $content = file_get_contents($backup->file_path);
            if ($content !== false) {
                return $content;
            }
        }

        // If local file doesn't exist or failed to read, try Google Drive
        if ($backup->google_drive_file_id && $this->googleDrive) {
            \Log::info("Local file not found, attempting to download from Google Drive: {$backup->google_drive_file_id}");

            try {
                // Create temporary file path
                $tempDir = storage_path('app/temp');
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                $tempPath = $tempDir . '/' . basename($backup->file_path ?: 'backup_' . $backup->id . '.sql');

                // Download from Google Drive
                if ($this->googleDrive->downloadFile($backup->google_drive_file_id, $tempPath)) {
                    \Log::info("Successfully downloaded backup from Google Drive to: {$tempPath}");

                    // Read the downloaded file
                    $content = file_get_contents($tempPath);

                    // Update the backup record with the local path for future use
                    $backup->update(['file_path' => $tempPath]);

                    return $content !== false ? $content : '';
                } else {
                    throw new Exception("Failed to download backup file from Google Drive");
                }
            } catch (Exception $e) {
                \Log::error("Google Drive download failed: " . $e->getMessage());
                throw new Exception("Failed to download backup from cloud storage: " . $e->getMessage());
            }
        }

        // If we get here, no backup file was found anywhere
        throw new Exception('Backup file not found locally or in cloud storage. File path: ' . ($backup->file_path ?: 'N/A') . ', Google Drive ID: ' . ($backup->google_drive_file_id ?: 'N/A'));
    }

    /**
     * Properly split SQL content into statements
     */
    private function splitSqlStatements(string $sqlContent): array
    {
        $statements = [];
        $currentStatement = '';
        $inQuotes = false;
        $quoteChar = '';
        
        // Normalize line endings and split into lines
        $sqlContent = str_replace(["\r\n", "\r"], "\n", $sqlContent);
        $lines = explode("\n", $sqlContent);

        foreach ($lines as $lineNumber => $line) {
            $originalLine = $line;
            $line = trim($line);

            // Skip comments and empty lines, but only when not inside a statement
            if (empty($line) || (empty($currentStatement) && (str_starts_with($line, '--') || str_starts_with($line, '/*')))) {
                continue;
            }

            // If we're building a statement, preserve the space/newline structure
            if (!empty($currentStatement)) {
                $currentStatement .= ' '; // Add space instead of newline to keep statement intact
            }

            // Process character by character for proper quote handling
            for ($i = 0; $i < strlen($line); $i++) {
                $char = $line[$i];

                // Handle quote state changes
                if (!$inQuotes && ($char === '"' || $char === "'")) {
                    $inQuotes = true;
                    $quoteChar = $char;
                } elseif ($inQuotes && $char === $quoteChar) {
                    // Check if it's an escaped quote (look for odd number of preceding backslashes)
                    $backslashCount = 0;
                    $j = $i - 1;
                    while ($j >= 0 && $line[$j] === '\\') {
                        $backslashCount++;
                        $j--;
                    }
                    
                    // If even number of backslashes (including 0), the quote is not escaped
                    if ($backslashCount % 2 === 0) {
                        $inQuotes = false;
                        $quoteChar = '';
                    }
                }

                $currentStatement .= $char;

                // If we hit a semicolon and we're not in quotes, this ends a statement
                if ($char === ';' && !$inQuotes) {
                    $stmt = trim($currentStatement);
                    if (!empty($stmt)) {
                        $statements[] = $stmt;
                        \Log::info("Split SQL statement: " . substr($stmt, 0, 100) . (strlen($stmt) > 100 ? '...' : ''));
                    }
                    $currentStatement = '';
                }
            }
        }

        // Add any remaining statement (might not end with semicolon)
        $stmt = trim($currentStatement);
        if (!empty($stmt)) {
            if (!str_ends_with($stmt, ';')) {
                \Log::warning("SQL statement doesn't end with semicolon, adding it: " . substr($stmt, 0, 50) . '...');
                $stmt .= ';';
            }
            $statements[] = $stmt;
        }

        // Filter out invalid statements
        $validStatements = array_filter($statements, function($statement) {
            $statement = trim($statement);
            $isEmpty = empty($statement);
            $isComment = str_starts_with($statement, '--') || str_starts_with($statement, '/*');
            $isValid = !$isEmpty && !$isComment && strlen($statement) > 5; // Minimum meaningful statement length
            
            if (!$isValid && !$isEmpty && !$isComment) {
                \Log::warning("Filtering out invalid SQL statement: " . substr($statement, 0, 50) . '...');
            }
            
            return $isValid;
        });

        \Log::info("Split SQL into " . count($validStatements) . " statements");
        return array_values($validStatements);
    }

    /**
     * Validate INSERT statement syntax
     */
    private function isValidInsertStatement(string $statement): bool
    {
        $statement = trim($statement);

        // Basic validation checks
        if (!str_starts_with(strtoupper($statement), 'INSERT INTO')) {
            return false;
        }

        // Check that statement has proper structure (either VALUES or column-based format)
        $upperStatement = strtoupper($statement);
        if (!preg_match('/INSERT INTO `[^`]+`\s*\([^)]+\)\s*VALUES\s*/', $statement) &&
            !preg_match('/INSERT INTO `[^`]+` VALUES\s*\(/', $statement)) {
            \Log::warning("INSERT statement doesn't match expected patterns: " . substr($statement, 0, 100));
            return false;
        }

        // Check for balanced parentheses
        $openParens = substr_count($statement, '(');
        $closeParens = substr_count($statement, ')');
        if ($openParens !== $closeParens || $openParens < 1) {
            \Log::warning("Unbalanced parentheses in INSERT statement: open={$openParens}, close={$closeParens}");
            return false;
        }

        // Check that it ends properly
        if (!str_ends_with($statement, ';')) {
            \Log::warning("INSERT statement doesn't end with semicolon");
            return false;
        }

        // Check for incomplete VALUES clauses
        if (str_contains($upperStatement, 'VALUES') && !str_contains($statement, '(')) {
            \Log::warning("INSERT statement has VALUES but no opening parenthesis");
            return false;
        }

        // Additional check for common malformed patterns
        if (str_contains($statement, 'VALUES ()') || str_contains($statement, 'VALUES()')) {
            \Log::warning("INSERT statement has empty VALUES clause");
            return false;
        }

        return true;
    }

    /**
     * Order INSERT statements to respect foreign key dependencies
     * Parent tables should be inserted before child tables that reference them
     */
    private function orderInsertStatements(array $statements): array
    {
        // Define table dependency order (parent tables first)
        $tableOrder = [
            'users',           // Must be first (referenced by many tables)
            'patients',        // Referenced by prenatal_records, child_records
            'vaccines',        // Referenced by immunizations, child_immunizations, stock_transactions
            'prenatal_records', // References patients
            'child_records',   // References patients
            'prenatal_checkups', // References prenatal_records
            'immunizations',   // References vaccines, child_records
            'child_immunizations', // References vaccines, child_records
            'stock_transactions', // References vaccines
            'notifications',   // References users (polymorphic)
            'cloud_backups',   // References users (created_by)
        ];

        // Group statements by table
        $statementsByTable = [];
        $otherStatements = [];
        
        foreach ($statements as $statement) {
            $tableName = $this->extractTableNameFromInsert($statement);
            if ($tableName) {
                if (!isset($statementsByTable[$tableName])) {
                    $statementsByTable[$tableName] = [];
                }
                $statementsByTable[$tableName][] = $statement;
            } else {
                $otherStatements[] = $statement;
            }
        }
        
        // Order statements according to dependency order
        $orderedStatements = [];
        
        // First, add statements for tables in the defined order
        foreach ($tableOrder as $tableName) {
            if (isset($statementsByTable[$tableName])) {
                $orderedStatements = array_merge($orderedStatements, $statementsByTable[$tableName]);
                unset($statementsByTable[$tableName]);
            }
        }
        
        // Then add any remaining table statements
        foreach ($statementsByTable as $tableName => $tableStatements) {
            \Log::info("Adding unordered table statements for: {$tableName}");
            $orderedStatements = array_merge($orderedStatements, $tableStatements);
        }
        
        // Finally add any other statements
        $orderedStatements = array_merge($orderedStatements, $otherStatements);
        
        return $orderedStatements;
    }
    
    /**
     * Extract table name from INSERT statement
     */
    private function extractTableNameFromInsert(string $statement): ?string
    {
        // Match INSERT INTO `table_name` or INSERT INTO table_name
        if (preg_match('/INSERT INTO `?([a-zA-Z_][a-zA-Z0-9_]*)`?/i', $statement, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get list of tables that have INSERT statements (tables being restored)
     */
    private function getTablesFromInsertStatements(array $dmlStatements): array
    {
        $tables = [];
        foreach ($dmlStatements as $statement) {
            $tableName = $this->extractTableNameFromInsert($statement);
            if ($tableName) {
                $tables[] = $tableName;
            }
        }
        return array_unique($tables);
    }

    /**
     * Order tables for clearing in dependency order (child tables first)
     * This ensures foreign key constraints aren't violated when clearing
     */
    private function orderTablesForClearing(array $tablesToClear): array
    {
        // Define dependency order for clearing (reverse of insert order)
        $clearingOrder = [
            // Child tables with foreign keys first (reverse dependency order)
            'stock_transactions',     // References vaccines
            'child_immunizations',    // References vaccines, child_records
            'immunizations',          // References vaccines, child_records
            'prenatal_checkups',      // References prenatal_records
            'cloud_backups',          // References users (created_by)
            'notifications',          // References users (polymorphic)
            'prenatal_records',       // References patients
            'child_records',          // References patients
            // Then parent tables
            'patients',               // Referenced by prenatal_records, child_records
            'vaccines',               // Referenced by immunizations, child_immunizations, stock_transactions
            'users',                  // Referenced by many tables
        ];

        // Filter and order only the tables that need to be cleared
        $orderedTables = [];
        foreach ($clearingOrder as $tableName) {
            if (in_array($tableName, $tablesToClear)) {
                $orderedTables[] = $tableName;
            }
        }

        // Add any remaining tables that weren't in our predefined order
        foreach ($tablesToClear as $tableName) {
            if (!in_array($tableName, $orderedTables)) {
                $orderedTables[] = $tableName;
            }
        }

        return $orderedTables;
    }
}
