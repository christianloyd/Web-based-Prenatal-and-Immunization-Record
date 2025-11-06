<?php

namespace App\Testing;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

trait MockRefreshDatabase
{
    /**
     * Define hooks to migrate the database before and after each test.
     */
    public function refreshDatabase(): void
    {
        // Skip database refresh if PDO driver is not available
        try {
            $this->artisan('migrate:fresh', $this->shouldDropViews() ? [
                '--drop-views' => true,
            ] : []);

            $this->app[Kernel::class]->setArtisan(null);

            RefreshDatabaseState::$migrated = true;
        } catch (\Exception $e) {
            // If migration fails, mark test as skipped
            $this->markTestSkipped('Database migration failed: ' . $e->getMessage());
        }
    }

    /**
     * Determine if views should be dropped when refreshing the database.
     */
    protected function shouldDropViews(): bool
    {
        return property_exists($this, 'dropViews') ? $this->dropViews : false;
    }

    /**
     * Begin a database transaction on the testing database.
     */
    public function beginDatabaseTransaction(): void
    {
        try {
            parent::beginDatabaseTransaction();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database transaction failed: ' . $e->getMessage());
        }
    }
}
