<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
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
        // Register Repository Bindings
        $this->registerRepositories();

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
        // Force HTTPS in production environment
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            
            // Trust all proxies (for Railway)
            request()->server->set('HTTPS', 'on');
        }

        // Use Tailwind for pagination
        Paginator::useTailwind();

        View::composer('*childadd*', ChildRecordComposer::class);

        // Register model observers for automatic notifications
        Patient::observe(PatientObserver::class);
        Vaccine::observe(VaccineObserver::class);
        PrenatalCheckup::observe(PrenatalCheckupObserver::class);

        // Register custom Blade directives for role-based content
        $this->registerRoleBladeDirectives();
    }

    /**
     * Register custom Blade directives for role-based views
     */
    private function registerRoleBladeDirectives()
    {
        // @midwife directive - shows content only to midwives
        Blade::if('midwife', function () {
            return auth()->check() && auth()->user()->role === 'midwife';
        });

        // @bhw directive - shows content only to BHWs
        Blade::if('bhw', function () {
            return auth()->check() && auth()->user()->role === 'bhw';
        });

        // @roleRoute directive - generates role-based route
        Blade::directive('roleRoute', function ($expression) {
            return "<?php echo route(auth()->user()->role . '.' . {$expression}); ?>";
        });

        // @roleCss directive - generates role-based CSS path
        Blade::directive('roleCss', function ($expression) {
            return "<?php echo asset('css/' . auth()->user()->role . '/' . {$expression}); ?>";
        });

        // @roleJs directive - generates role-based JS path
        Blade::directive('roleJs', function ($expression) {
            return "<?php echo asset('js/' . auth()->user()->role . '/' . {$expression}); ?>";
        });
    }

    /**
     * Register all repository bindings
     *
     * @return void
     */
    private function registerRepositories(): void
    {
        // Patient Repository (already exists)
        $this->app->bind(
            \App\Repositories\Contracts\PatientRepositoryInterface::class,
            \App\Repositories\PatientRepository::class
        );

        // Prenatal Record Repository (already exists)
        $this->app->bind(
            \App\Repositories\Contracts\PrenatalRecordRepositoryInterface::class,
            \App\Repositories\PrenatalRecordRepository::class
        );

        // Child Record Repository (already exists)
        $this->app->bind(
            \App\Repositories\Contracts\ChildRecordRepositoryInterface::class,
            \App\Repositories\ChildRecordRepository::class
        );

        // User Repository
        $this->app->bind(
            \App\Repositories\Contracts\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );

        // Vaccine Repository
        $this->app->bind(
            \App\Repositories\Contracts\VaccineRepositoryInterface::class,
            \App\Repositories\VaccineRepository::class
        );

        // Immunization Repository
        $this->app->bind(
            \App\Repositories\Contracts\ImmunizationRepositoryInterface::class,
            \App\Repositories\ImmunizationRepository::class
        );

        // Prenatal Checkup Repository
        $this->app->bind(
            \App\Repositories\Contracts\PrenatalCheckupRepositoryInterface::class,
            \App\Repositories\PrenatalCheckupRepository::class
        );

        // Appointment Repository
        $this->app->bind(
            \App\Repositories\Contracts\AppointmentRepositoryInterface::class,
            \App\Repositories\AppointmentRepository::class
        );

        // Child Immunization Repository
        $this->app->bind(
            \App\Repositories\Contracts\ChildImmunizationRepositoryInterface::class,
            \App\Repositories\ChildImmunizationRepository::class
        );

        // Cloud Backup Repository
        $this->app->bind(
            \App\Repositories\Contracts\CloudBackupRepositoryInterface::class,
            \App\Repositories\CloudBackupRepository::class
        );

        // Stock Transaction Repository
        $this->app->bind(
            \App\Repositories\Contracts\StockTransactionRepositoryInterface::class,
            \App\Repositories\StockTransactionRepository::class
        );

        // Prenatal Visit Repository
        $this->app->bind(
            \App\Repositories\Contracts\PrenatalVisitRepositoryInterface::class,
            \App\Repositories\PrenatalVisitRepository::class
        );

        // Restore Operation Repository
        $this->app->bind(
            \App\Repositories\Contracts\RestoreOperationRepositoryInterface::class,
            \App\Repositories\RestoreOperationRepository::class
        );

        // SMS Log Repository
        $this->app->bind(
            \App\Repositories\Contracts\SmsLogRepositoryInterface::class,
            \App\Repositories\SmsLogRepository::class
        );
        
    }
    
}