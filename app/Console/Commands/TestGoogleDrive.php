<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleDriveService;
use App\Services\DatabaseBackupService;

class TestGoogleDrive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:google-drive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Google Drive connection and functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Google Drive Connection...');
        
        try {
            // Test service binding
            $googleDriveService = app(GoogleDriveService::class);
            
            if (!$googleDriveService) {
                $this->error('GoogleDriveService is null - service binding failed');
                return;
            }
            
            $this->info('GoogleDriveService binding: SUCCESS');
            
            // Test connection
            $this->info('Testing connection...');
            $connected = $googleDriveService->testConnection();
            
            if ($connected) {
                $this->info('Google Drive connection: SUCCESS');
                
                // Test storage info
                $this->info('Getting storage information...');
                $storageInfo = $googleDriveService->getStorageQuota();
                $this->info('Storage info: ' . json_encode($storageInfo));
                
            } else {
                $this->error('Google Drive connection: FAILED');
            }
            
            // Test DatabaseBackupService
            $this->info('Testing DatabaseBackupService...');
            $backupService = app(DatabaseBackupService::class);
            $testConnection = $backupService->testGoogleDriveConnection();
            
            $this->info('DatabaseBackupService Google Drive test: ' . ($testConnection ? 'SUCCESS' : 'FAILED'));
            
            // Test actual file upload
            if ($connected) {
                $this->info('Testing file upload...');
                
                // Create a test file
                $testFilePath = storage_path('app/test_backup.sql');
                file_put_contents($testFilePath, "-- Test backup file\nCREATE TABLE test (id INT);");
                
                try {
                    $uploadResult = $googleDriveService->uploadFile($testFilePath, 'test_backup.sql', ['test' => true]);
                    $this->info('File upload: SUCCESS');
                    $this->info('Upload result: ' . json_encode($uploadResult));
                    
                    // Clean up test file
                    unlink($testFilePath);
                    
                    // Delete uploaded test file
                    $googleDriveService->deleteFile($uploadResult['file_id']);
                    $this->info('Test file cleaned up successfully');
                    
                } catch (\Exception $e) {
                    $this->error('File upload failed: ' . $e->getMessage());
                    if (file_exists($testFilePath)) {
                        unlink($testFilePath);
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->error('Exception occurred: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
        }
    }
}
