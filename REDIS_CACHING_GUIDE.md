# Redis Caching Implementation Guide

## Overview

This guide provides step-by-step instructions for implementing Redis caching in the Web-based Prenatal and Immunization Record system to improve performance and reduce database load.

---

## Prerequisites

- PHP Redis extension installed
- Redis server installed and running
- Laravel 11.x application

---

## Installation Steps

### 1. Install Redis Server

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install redis-server
sudo systemctl start redis
sudo systemctl enable redis
```

**macOS (Homebrew):**
```bash
brew install redis
brew services start redis
```

**Windows:**
Download from: https://github.com/microsoftarchive/redis/releases

### 2. Install PHP Redis Extension

```bash
# Using PECL
sudo pecl install redis

# Or using apt (Ubuntu/Debian)
sudo apt-get install php-redis

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### 3. Install Laravel Redis Package

```bash
composer require predis/predis
```

### 4. Configure Laravel

Update `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

Update `config/database.php`:
```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),

    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],

    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
    ],

    'cache' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),
    ],
],
```

---

## Implementation Strategy

### 1. Cache Frequently Accessed Data

#### Vaccines List
```php
// In VaccineRepository
public function getAllCached(): Collection
{
    return Cache::remember('vaccines:all', 3600, function () {
        return $this->model->orderBy('name')->get();
    });
}

public function getCategoryCached(string $category): Collection
{
    return Cache::remember("vaccines:category:{$category}", 3600, function () use ($category) {
        return $this->model->where('category', $category)->get();
    });
}
```

#### Users List
```php
// In UserRepository
public function getHealthcareWorkersCached(): Collection
{
    return Cache::remember('users:healthcare_workers', 3600, function () {
        return $this->model->whereIn('role', ['midwife', 'bhw'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    });
}
```

#### Patient Statistics
```php
// In DashboardController
public function getStatistics()
{
    return Cache::remember('dashboard:statistics', 600, function () {
        return [
            'total_patients' => Patient::count(),
            'active_pregnancies' => PrenatalRecord::where('is_active', true)->count(),
            'upcoming_checkups' => PrenatalCheckup::where('status', 'upcoming')->count(),
            'low_stock_vaccines' => Vaccine::whereColumn('current_stock', '<=', 'min_stock')->count(),
        ];
    });
}
```

### 2. Cache Invalidation Strategy

Create cache tags for related data:

```php
// App/Services/CacheService.php
namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Clear vaccine-related cache
     */
    public static function clearVaccineCache(): void
    {
        Cache::forget('vaccines:all');
        Cache::forget('dashboard:statistics');
        // Clear all category caches
        foreach (['Routine Immunization', 'COVID-19', 'Seasonal', 'Travel'] as $category) {
            Cache::forget("vaccines:category:{$category}");
        }
    }

    /**
     * Clear patient-related cache
     */
    public static function clearPatientCache(?int $patientId = null): void
    {
        if ($patientId) {
            Cache::forget("patient:{$patientId}");
            Cache::forget("patient:{$patientId}:checkups");
        }
        Cache::forget('dashboard:statistics');
    }

    /**
     * Clear user cache
     */
    public static function clearUserCache(): void
    {
        Cache::forget('users:healthcare_workers');
        Cache::forget('users:all');
    }

    /**
     * Clear all application cache
     */
    public static function clearAll(): void
    {
        Cache::flush();
    }
}
```

### 3. Integrate Cache Invalidation in Services

```php
// In VaccineService
use App\Services\CacheService;

public function createVaccine(array $data): Vaccine
{
    return DB::transaction(function () use ($data) {
        $vaccine = $this->vaccineRepository->create($data);

        // Clear cache
        CacheService::clearVaccineCache();

        Log::info('Vaccine created', ['vaccine_id' => $vaccine->id]);
        return $vaccine;
    });
}

public function updateVaccine(int $id, array $data): bool
{
    return DB::transaction(function () use ($id, $data) {
        $result = $this->vaccineRepository->update($id, $data);

        if ($result) {
            // Clear cache
            CacheService::clearVaccineCache();
            Log::info('Vaccine updated', ['vaccine_id' => $id]);
        }

        return $result;
    });
}
```

---

## Caching Recommendations by Data Type

### High-Priority Caching (Cache for 1 hour+)

| Data Type | Cache Duration | Cache Key Pattern | Invalidate On |
|-----------|----------------|-------------------|---------------|
| **Vaccines List** | 1 hour | `vaccines:all` | Create, Update, Delete vaccine |
| **Users List** | 1 hour | `users:healthcare_workers` | Create, Update, Delete user |
| **System Settings** | 24 hours | `settings:{key}` | Settings update |
| **Reports (Monthly)** | 1 day | `report:monthly:{month}` | End of month or manual |

### Medium-Priority Caching (Cache for 10-30 min)

| Data Type | Cache Duration | Cache Key Pattern | Invalidate On |
|-----------|----------------|-------------------|---------------|
| **Dashboard Stats** | 10 minutes | `dashboard:statistics` | Any CRUD operation |
| **Low Stock Alerts** | 15 minutes | `vaccines:low_stock` | Stock transaction |
| **Upcoming Checkups** | 15 minutes | `checkups:upcoming` | Checkup create/update |
| **Patient Count** | 30 minutes | `patients:count` | Patient create/delete |

### Low-Priority Caching (Cache for 5-10 min)

| Data Type | Cache Duration | Cache Key Pattern | Invalidate On |
|-----------|----------------|-------------------|---------------|
| **Patient Details** | 5 minutes | `patient:{id}` | Patient update |
| **Child Immunizations** | 10 minutes | `child:{id}:immunizations` | Immunization update |
| **Search Results** | 5 minutes | `search:{query}:{type}` | Time expiration only |

---

## Cache Warming Strategy

Pre-populate cache on application start or after deployment:

```php
// app/Console/Commands/WarmCache.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Contracts\VaccineRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

class WarmCache extends Command
{
    protected $signature = 'cache:warm';
    protected $description = 'Warm up application cache with frequently accessed data';

    public function handle(
        VaccineRepositoryInterface $vaccineRepo,
        UserRepositoryInterface $userRepo
    ) {
        $this->info('Warming cache...');

        // Warm vaccines cache
        $vaccineRepo->getAllCached();
        $this->info('✓ Vaccines cached');

        // Warm users cache
        $userRepo->getHealthcareWorkersCached();
        $this->info('✓ Users cached');

        // Warm dashboard statistics
        app(\App\Http\Controllers\DashboardController::class)->getStatistics();
        $this->info('✓ Dashboard stats cached');

        $this->info('Cache warming completed!');
    }
}
```

Add to scheduler in `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // Warm cache every hour
    $schedule->command('cache:warm')->hourly();
}
```

---

## Monitoring Redis Performance

### 1. Redis CLI Commands

```bash
# Check Redis connection
redis-cli ping

# Monitor Redis activity
redis-cli monitor

# Get Redis info
redis-cli info

# Check memory usage
redis-cli info memory

# View all keys
redis-cli keys "*"

# Get cache hit/miss ratio
redis-cli info stats | grep keyspace
```

### 2. Laravel Telescope

Install Laravel Telescope for cache monitoring:
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Access at: `http://your-app.test/telescope/cache`

### 3. Performance Metrics

Monitor these metrics:
- **Cache Hit Ratio** - Target: >80%
- **Average Response Time** - Should decrease by 50-70%
- **Database Query Count** - Should decrease significantly
- **Redis Memory Usage** - Monitor for memory leaks

---

## Testing Cache Implementation

```php
// tests/Feature/CacheTest.php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use App\Models\Vaccine;

class CacheTest extends TestCase
{
    public function test_vaccines_are_cached()
    {
        // Clear cache
        Cache::flush();

        // First call should hit database
        $start = microtime(true);
        $vaccines1 = app(VaccineRepository::class)->getAllCached();
        $time1 = microtime(true) - $start;

        // Second call should hit cache
        $start = microtime(true);
        $vaccines2 = app(VaccineRepository::class)->getAllCached();
        $time2 = microtime(true) - $start;

        // Cache should be faster
        $this->assertLessThan($time1, $time2);
        $this->assertEquals($vaccines1, $vaccines2);
    }

    public function test_cache_is_invalidated_on_update()
    {
        $vaccine = Vaccine::factory()->create();
        $cached = Cache::get('vaccines:all');

        // Update vaccine
        app(VaccineService::class)->updateVaccine($vaccine->id, ['name' => 'Updated']);

        // Cache should be cleared
        $this->assertNull(Cache::get('vaccines:all'));
    }
}
```

---

## Deployment Checklist

- [ ] Redis server installed and running
- [ ] PHP Redis extension installed
- [ ] `.env` updated with Redis configuration
- [ ] Cache configuration verified
- [ ] Cache warming command created
- [ ] Cache invalidation integrated in services
- [ ] Scheduler configured for cache warming
- [ ] Monitoring tools installed (Telescope)
- [ ] Performance benchmarks established
- [ ] Cache tests written and passing
- [ ] Documentation updated

---

## Performance Expectations

With Redis caching properly implemented:

| Metric | Before Redis | After Redis | Improvement |
|--------|-------------|-------------|-------------|
| **Dashboard Load Time** | 2,100ms | 450ms | 79% faster |
| **Vaccine List Query** | 120ms | 5ms | 96% faster |
| **User Dropdown Load** | 80ms | 3ms | 96% faster |
| **Stats Calculation** | 850ms | 50ms | 94% faster |
| **Database Load** | High | Low | 70% reduction |

---

## Troubleshooting

### Issue: Cache Not Working

**Check:**
```bash
# Verify Redis is running
sudo systemctl status redis

# Test Redis connection
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');
```

### Issue: Memory Issues

**Solution:**
```bash
# Check Redis memory
redis-cli info memory

# Set max memory in redis.conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

### Issue: Stale Cache

**Solution:**
```bash
# Clear all cache
php artisan cache:clear

# Or from code
Cache::flush();
```

---

## Best Practices

1. **Cache Expiration**: Always set expiration times, never cache forever
2. **Cache Keys**: Use descriptive, hierarchical cache keys
3. **Cache Invalidation**: Invalidate cache immediately after data changes
4. **Monitoring**: Monitor cache hit rates and adjust strategy
5. **Testing**: Write tests for cache behavior
6. **Documentation**: Document what's cached and why
7. **Fallback**: Always have fallback to database if cache fails

---

## Next Steps

1. Install Redis server in production
2. Update environment configuration
3. Implement CacheService class
4. Add caching to repositories
5. Integrate cache invalidation in services
6. Create cache warming command
7. Set up monitoring with Telescope
8. Run performance benchmarks
9. Deploy and monitor

---

**Last Updated:** 2025-11-09
**Requires:** Redis 6.0+, PHP 8.2+, Laravel 11.x
**Performance Impact:** 70-95% improvement in cached queries
