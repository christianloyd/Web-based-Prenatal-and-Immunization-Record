<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;
use App\Models\Patient;
use App\Models\PrenatalRecord;
use App\Models\Immunization;
use App\Models\Vaccine;

class WarmCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up application cache with frequently accessed data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cache warm-up...');
        $this->newLine();

        // Warm vaccines cache
        $this->info('Caching vaccines...');
        CacheService::getActiveVaccines();
        $this->line('  ✓ Vaccines cached');

        // Warm users cache
        $this->info('Caching users...');
        CacheService::getActiveUsers();
        CacheService::getUsersByRole('midwife');
        CacheService::getUsersByRole('bhw');
        $this->line('  ✓ Users cached');
        $this->line('  ✓ Healthcare workers cached');

        // Warm dashboard statistics
        $this->info('Caching dashboard statistics...');
        CacheService::getDashboardStats('midwife');
        CacheService::getDashboardStats('bhw');
        CacheService::getDashboardStats('admin');
        $this->line('  ✓ Dashboard stats cached');

        // Cache low stock vaccines
        $this->info('Caching low stock alerts...');
        \Illuminate\Support\Facades\Cache::remember('vaccines:low_stock', 900, function () {
            return Vaccine::whereColumn('current_stock', '<=', 'min_stock')->get();
        });
        $this->line('  ✓ Low stock alerts cached');

        // Cache patient counts
        $this->info('Caching patient statistics...');
        \Illuminate\Support\Facades\Cache::remember('patients:total_count', 1800, function () {
            return Patient::count();
        });
        \Illuminate\Support\Facades\Cache::remember('prenatal:active_count', 1800, function () {
            return PrenatalRecord::where('is_active', true)->count();
        });
        $this->line('  ✓ Patient statistics cached');

        $this->newLine();
        $this->info('Cache warm-up completed successfully!');

        return Command::SUCCESS;
    }
}
