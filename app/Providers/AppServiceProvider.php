<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use App\View\Composers\ChildRecordComposer;
use App\Models\Patient;
use App\Models\Vaccine;
use App\Models\PrenatalCheckup;
use App\Observers\PatientObserver;
use App\Observers\VaccineObserver;
use App\Observers\PrenatalCheckupObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind GoogleDriveService conditionally
        $this->app->bind(\App\Services\GoogleDriveService::class, function ($app) {
            $oauthCredentialsPath = storage_path('app/google/oauth_credentials.json');
            $serviceCredentialsPath = storage_path('app/google/credentials.json');
            
            // Only initialize if we have credentials
            if (file_exists($oauthCredentialsPath) || file_exists($serviceCredentialsPath)) {
                try {
                    \Log::info('Attempting to initialize GoogleDriveService...');
                    $service = new \App\Services\GoogleDriveService();
                    \Log::info('GoogleDriveService initialized successfully');
                    return $service;
                } catch (\Exception $e) {
                    \Log::error('GoogleDriveService initialization failed: ' . $e->getMessage());
                    \Log::error('Exception trace: ' . $e->getTraceAsString());
                    
                    // For OAuth errors, still return the service object but it may not be fully functional
                    // This allows other methods to work (like isAuthenticated checks)
                    try {
                        $service = new \App\Services\GoogleDriveService();
                        \Log::info('Created GoogleDriveService despite initialization error');
                        return $service;
                    } catch (\Exception $e2) {
                        \Log::error('Complete GoogleDriveService failure: ' . $e2->getMessage());
                        return null;
                    }
                }
            }
            
            \Log::info('No Google Drive credentials found, service not initialized');
            return null;
        });

        // Bind DatabaseBackupService with optional GoogleDriveService
        $this->app->bind(\App\Services\DatabaseBackupService::class, function ($app) {
            $googleDriveService = $app->make(\App\Services\GoogleDriveService::class);
            return new \App\Services\DatabaseBackupService($googleDriveService);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Use Tailwind for pagination
        Paginator::useTailwind();

        View::composer('*childadd*', ChildRecordComposer::class);

        // Register model observers for automatic notifications
        Patient::observe(PatientObserver::class);
        Vaccine::observe(VaccineObserver::class);
        PrenatalCheckup::observe(PrenatalCheckupObserver::class);
    }
}
