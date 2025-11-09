<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;
use Illuminate\Support\Facades\Redis;

class CacheStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display current cache status and statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Redis Cache Status ===');
        $this->newLine();

        // Check Redis connection
        try {
            $redis = Redis::connection();
            $redis->ping();
            $this->line('<info>✓</info> Redis Connection: <comment>Connected</comment>');
        } catch (\Exception $e) {
            $this->line('<error>✗</error> Redis Connection: <error>Failed</error>');
            return Command::FAILURE;
        }

        $this->newLine();

        // Get cache statistics
        $stats = CacheService::getCacheStats();

        $this->info('Cached Keys Status:');
        $this->newLine();

        $cachedCount = 0;
        $totalCount = count($stats);

        foreach ($stats as $key => $isCached) {
            $status = $isCached ? '<info>✓ Cached</info>' : '<comment>✗ Not Cached</comment>';
            $this->line(sprintf('  %-30s %s', $key, $status));
            if ($isCached) {
                $cachedCount++;
            }
        }

        $this->newLine();

        $percentage = $totalCount > 0 ? round(($cachedCount / $totalCount) * 100, 1) : 0;
        $this->info("Cache Hit Rate: {$cachedCount}/{$totalCount} ({$percentage}%)");

        // Redis memory info
        try {
            $info = $redis->info('memory');
            if (isset($info['used_memory_human'])) {
                $this->newLine();
                $this->info('Redis Memory Usage:');
                $this->line("  Used: {$info['used_memory_human']}");
                if (isset($info['maxmemory_human']) && $info['maxmemory_human'] !== '0B') {
                    $this->line("  Max: {$info['maxmemory_human']}");
                }
            }
        } catch (\Exception $e) {
            // Silently fail if we can't get memory info
        }

        $this->newLine();
        $this->line('<comment>Tip:</comment> Run <info>php artisan cache:warm</info> to populate the cache');

        return Command::SUCCESS;
    }
}
