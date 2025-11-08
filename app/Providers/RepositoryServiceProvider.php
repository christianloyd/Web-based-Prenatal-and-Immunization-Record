<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repository Contracts
use App\Repositories\Contracts\PatientRepositoryInterface;
use App\Repositories\Contracts\PrenatalRecordRepositoryInterface;
use App\Repositories\Contracts\ChildRecordRepositoryInterface;

// Repository Implementations
use App\Repositories\PatientRepository;
use App\Repositories\PrenatalRecordRepository;
use App\Repositories\ChildRecordRepository;

/**
 * Repository Service Provider
 *
 * Binds repository interfaces to their concrete implementations
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services
     *
     * @return void
     */
    public function register(): void
    {
        // Bind Patient Repository
        $this->app->bind(
            PatientRepositoryInterface::class,
            PatientRepository::class
        );

        // Bind Prenatal Record Repository
        $this->app->bind(
            PrenatalRecordRepositoryInterface::class,
            PrenatalRecordRepository::class
        );

        // Bind Child Record Repository
        $this->app->bind(
            ChildRecordRepositoryInterface::class,
            ChildRecordRepository::class
        );

        // Add more repository bindings here as needed
        // Example:
        // $this->app->bind(
        //     VaccineRepositoryInterface::class,
        //     VaccineRepository::class
        // );
    }

    /**
     * Bootstrap services
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
