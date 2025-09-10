<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Exception;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    private Client $client;
    private Drive $service;
    private string $folderId;

    public function __construct()
    {
        $this->initializeClient();
        $this->folderId = $this->getOrCreateBackupFolder();
    }

    /**
     * Initialize Google Client
     */
    private function initializeClient(): void
    {
        $this->client = new Client();
        $this->client->setApplicationName('Healthcare Backup System');
        $this->client->setScopes([Drive::DRIVE_FILE]);
        
        // Configure HTTP client with SSL options for development
        $httpClient = new \GuzzleHttp\Client([
            'verify' => false, // Disable SSL verification for development
            'timeout' => 60,
            'connect_timeout' => 30
        ]);
        $this->client->setHttpClient($httpClient);
        
        // Check for OAuth2 credentials first, then fall back to service account
        $oauthCredentialsPath = storage_path('app/google/oauth_credentials.json');
        $serviceCredentialsPath = storage_path('app/google/credentials.json');
        
        if (file_exists($oauthCredentialsPath)) {
            // Use OAuth2 credentials
            $this->client->setAuthConfig($oauthCredentialsPath);
            $this->client->setRedirectUri($this->getRedirectUri());
            
            // Check for stored access token
            $tokenPath = storage_path('app/google/token.json');
            if (file_exists($tokenPath)) {
                $accessToken = json_decode(file_get_contents($tokenPath), true);
                $this->client->setAccessToken($accessToken);
                
                // Refresh token if expired
                if ($this->client->isAccessTokenExpired()) {
                    if ($this->client->getRefreshToken()) {
                        $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                        file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
                    } else {
                        throw new Exception('Google Drive token expired and no refresh token available. Please re-authenticate.');
                    }
                }
            } else {
                throw new Exception('Google Drive not authenticated. Please authenticate first.');
            }
        } elseif (file_exists($serviceCredentialsPath)) {
            // Fall back to service account (legacy)
            $this->client->setAuthConfig($serviceCredentialsPath);
        } else {
            throw new Exception('No Google Drive credentials found. Please add OAuth2 credentials or service account credentials.');
        }
        
        $this->service = new Drive($this->client);
    }

    /**
     * Get or create the backup folder in Google Drive
     */
    private function getOrCreateBackupFolder(): string
    {
        $folderName = 'Healthcare Backups';
        
        // Search for existing folder
        $response = $this->service->files->listFiles([
            'q' => "name='{$folderName}' and mimeType='application/vnd.google-apps.folder'",
            'fields' => 'files(id, name)'
        ]);

        $folders = $response->getFiles();
        
        if (count($folders) > 0) {
            return $folders[0]->getId();
        }

        // Create new folder
        $folder = new DriveFile();
        $folder->setName($folderName);
        $folder->setMimeType('application/vnd.google-apps.folder');
        
        $createdFolder = $this->service->files->create($folder, [
            'fields' => 'id'
        ]);

        return $createdFolder->getId();
    }

    /**
     * Upload a file to Google Drive
     */
    public function uploadFile(string $localFilePath, string $fileName, array $metadata = []): array
    {
        if (!file_exists($localFilePath)) {
            throw new Exception("Local file not found: {$localFilePath}");
        }

        try {
            $file = new DriveFile();
            $file->setName($fileName);
            $file->setParents([$this->folderId]);
            
            // Add metadata if provided
            if (!empty($metadata)) {
                $file->setDescription(json_encode($metadata));
            }

            $result = $this->service->files->create($file, [
                'data' => file_get_contents($localFilePath),
                'mimeType' => 'application/sql',
                'uploadType' => 'multipart',
                'fields' => 'id, name, size, createdTime, webViewLink'
            ]);

            Log::info("File uploaded to Google Drive", [
                'file_id' => $result->getId(),
                'file_name' => $fileName,
                'size' => $result->getSize()
            ]);

            return [
                'file_id' => $result->getId(),
                'file_name' => $result->getName(),
                'size' => $result->getSize(),
                'created_time' => $result->getCreatedTime(),
                'web_view_link' => $result->getWebViewLink(),
                'download_link' => $this->getDownloadLink($result->getId())
            ];

        } catch (Exception $e) {
            Log::error("Google Drive upload failed", [
                'error' => $e->getMessage(),
                'file' => $fileName
            ]);
            
            throw new Exception("Google Drive upload failed: " . $e->getMessage());
        }
    }

    /**
     * Download a file from Google Drive
     */
    public function downloadFile(string $fileId, string $localPath): bool
    {
        try {
            $response = $this->service->files->get($fileId, [
                'alt' => 'media'
            ]);

            $content = $response->getBody()->getContents();
            
            // Ensure directory exists
            $directory = dirname($localPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            file_put_contents($localPath, $content);
            
            Log::info("File downloaded from Google Drive", [
                'file_id' => $fileId,
                'local_path' => $localPath
            ]);

            return true;

        } catch (Exception $e) {
            Log::error("Google Drive download failed", [
                'error' => $e->getMessage(),
                'file_id' => $fileId
            ]);
            
            return false;
        }
    }

    /**
     * Delete a file from Google Drive
     */
    public function deleteFile(string $fileId): bool
    {
        try {
            $this->service->files->delete($fileId);
            
            Log::info("File deleted from Google Drive", [
                'file_id' => $fileId
            ]);

            return true;

        } catch (Exception $e) {
            Log::error("Google Drive delete failed", [
                'error' => $e->getMessage(),
                'file_id' => $fileId
            ]);
            
            return false;
        }
    }

    /**
     * Get file information from Google Drive
     */
    public function getFileInfo(string $fileId): ?array
    {
        try {
            $file = $this->service->files->get($fileId, [
                'fields' => 'id, name, size, createdTime, modifiedTime, webViewLink'
            ]);

            return [
                'id' => $file->getId(),
                'name' => $file->getName(),
                'size' => $file->getSize(),
                'created_time' => $file->getCreatedTime(),
                'modified_time' => $file->getModifiedTime(),
                'web_view_link' => $file->getWebViewLink(),
                'download_link' => $this->getDownloadLink($file->getId())
            ];

        } catch (Exception $e) {
            Log::error("Failed to get Google Drive file info", [
                'error' => $e->getMessage(),
                'file_id' => $fileId
            ]);
            
            return null;
        }
    }

    /**
     * List all backup files in the folder
     */
    public function listBackupFiles(): array
    {
        try {
            $response = $this->service->files->listFiles([
                'q' => "'{$this->folderId}' in parents and trashed=false",
                'fields' => 'files(id, name, size, createdTime, modifiedTime)',
                'orderBy' => 'createdTime desc'
            ]);

            $files = [];
            foreach ($response->getFiles() as $file) {
                $files[] = [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'size' => $file->getSize(),
                    'created_time' => $file->getCreatedTime(),
                    'modified_time' => $file->getModifiedTime(),
                    'download_link' => $this->getDownloadLink($file->getId())
                ];
            }

            return $files;

        } catch (Exception $e) {
            Log::error("Failed to list Google Drive files", [
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Get direct download link for a file
     */
    public function getDownloadLink(string $fileId): string
    {
        return "https://drive.google.com/uc?id={$fileId}&export=download";
    }

    /**
     * Get storage quota information
     */
    public function getStorageQuota(): array
    {
        try {
            $about = $this->service->about->get([
                'fields' => 'storageQuota'
            ]);

            $quota = $about->getStorageQuota();
            
            return [
                'limit' => $quota->getLimit(),
                'usage' => $quota->getUsage(),
                'usage_in_drive' => $quota->getUsageInDrive(),
                'available' => $quota->getLimit() - $quota->getUsage()
            ];

        } catch (Exception $e) {
            Log::error("Failed to get Google Drive storage quota", [
                'error' => $e->getMessage()
            ]);
            
            return [
                'limit' => 0,
                'usage' => 0,
                'usage_in_drive' => 0,
                'available' => 0
            ];
        }
    }

    /**
     * Test connection to Google Drive
     */
    public function testConnection(): bool
    {
        try {
            Log::info("Testing Google Drive connection...");
            $about = $this->service->about->get(['fields' => 'user,storageQuota']);
            Log::info("Google Drive connection successful", [
                'user' => $about->getUser() ? $about->getUser()->getDisplayName() : 'Service Account'
            ]);
            return true;
        } catch (Exception $e) {
            Log::error("Google Drive connection test failed", [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get the appropriate redirect URI (static helper method)
     */
    private static function getRedirectUriStatic(): string
    {
        // Check if we have a custom redirect URI in config
        $customRedirectUri = config('services.google.redirect_uri');
        if ($customRedirectUri) {
            return $customRedirectUri;
        }

        // Auto-detect based on current URL
        return url('/google/callback');
    }

    /**
     * Get OAuth2 authorization URL (static method that doesn't need full service initialization)
     */
    public static function getAuthUrl(): string
    {
        $oauthCredentialsPath = storage_path('app/google/oauth_credentials.json');
        
        if (!file_exists($oauthCredentialsPath)) {
            throw new Exception('OAuth2 credentials not found. Please add oauth_credentials.json to storage/app/google/');
        }
        
        $client = new Client();
        $client->setAuthConfig($oauthCredentialsPath);
        $client->setRedirectUri(self::getRedirectUriStatic());
        $client->setScopes([Drive::DRIVE_FILE]);
        $client->setAccessType('offline');
        $client->setPrompt('consent'); // Force to get refresh token
        
        // Configure HTTP client
        $httpClient = new \GuzzleHttp\Client([
            'verify' => false,
            'timeout' => 60,
            'connect_timeout' => 30
        ]);
        $client->setHttpClient($httpClient);
        
        return $client->createAuthUrl();
    }

    /**
     * Handle OAuth2 callback and store token (static method)
     */
    public static function handleCallback(string $code): bool
    {
        try {
            $oauthCredentialsPath = storage_path('app/google/oauth_credentials.json');
            
            $client = new Client();
            $client->setAuthConfig($oauthCredentialsPath);
            $client->setRedirectUri(self::getRedirectUriStatic());
            $client->setScopes([Drive::DRIVE_FILE]);
            
            // Configure HTTP client
            $httpClient = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 60,
                'connect_timeout' => 30
            ]);
            $client->setHttpClient($httpClient);
            
            // Exchange authorization code for access token
            $token = $client->fetchAccessTokenWithAuthCode($code);
            
            if (isset($token['error'])) {
                throw new Exception('Error fetching access token: ' . $token['error']);
            }
            
            // Save token to file
            $tokenPath = storage_path('app/google/token.json');
            file_put_contents($tokenPath, json_encode($token));
            
            Log::info('Google Drive OAuth2 authentication successful');
            return true;
            
        } catch (Exception $e) {
            Log::error('Google Drive OAuth2 callback failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if OAuth2 is authenticated
     */
    public function isAuthenticated(): bool
    {
        $tokenPath = storage_path('app/google/token.json');
        $oauthCredentialsPath = storage_path('app/google/oauth_credentials.json');
        
        if (!file_exists($oauthCredentialsPath) || !file_exists($tokenPath)) {
            return false;
        }
        
        try {
            $client = new Client();
            $client->setAuthConfig($oauthCredentialsPath);
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
            
            return !$client->isAccessTokenExpired() || $client->getRefreshToken();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Revoke authentication
     */
    public function revokeAuthentication(): bool
    {
        try {
            $tokenPath = storage_path('app/google/token.json');
            if (file_exists($tokenPath)) {
                unlink($tokenPath);
            }
            
            Log::info('Google Drive authentication revoked');
            return true;
        } catch (Exception $e) {
            Log::error('Failed to revoke Google Drive authentication: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the appropriate redirect URI based on environment
     */
    private function getRedirectUri(): string
    {
        return self::getRedirectUriStatic();
    }
}