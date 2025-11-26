# Project Improvement Plan & Task List
## Web-based Prenatal and Immunization Record System

**Date**: November 2025
**Based on**: Comprehensive Codebase Review & Analysis

---

## 📋 TABLE OF CONTENTS

1. [Critical Issues (Fix Immediately)](#critical-issues)
2. [High Priority Improvements](#high-priority)
3. [Medium Priority Enhancements](#medium-priority)
4. [Long-term Architecture Improvements](#long-term)
5. [Repository Pattern Implementation Guide](#repository-pattern)
6. [Backend Weaknesses & Fixes](#backend-weaknesses)
7. [Testing Strategy](#testing-strategy)
8. [Security Hardening](#security-hardening)
9. [Performance Optimization](#performance-optimization)
10. [Code Quality Improvements](#code-quality)

---

## 🚨 CRITICAL ISSUES (Fix Immediately)

### 1. Missing HTTPS Enforcement in Production
**Problem**: Application doesn't force HTTPS, exposing patient data in transit.

**Impact**: 🔴 **CRITICAL** - PHI (Protected Health Information) could be intercepted

**Solution**:
```php
// app/Http/Middleware/ForceHttps.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttps
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->secure() && app()->environment('production')) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}

// app/Http/Kernel.php - Add to $middleware array
\App\Http\Middleware\ForceHttps::class,
```

**Files to modify**:
- `app/Http/Middleware/ForceHttps.php` (create)
- `app/Http/Kernel.php` (add middleware)
- `config/session.php` (set `secure` => true for production)

**Time estimate**: 30 minutes
**Assigned to**: Backend Developer
**Priority**: 🔴 P0 - Critical

---

### 2. Missing Security Headers
**Problem**: No security headers to prevent XSS, clickjacking, MIME sniffing

**Impact**: 🔴 **HIGH** - Vulnerable to common web attacks

**Solution**:
```php
// app/Http/Middleware/SecurityHeaders.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Only in production
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy (adjust as needed)
        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
            "img-src 'self' data: https:; " .
            "font-src 'self' data: https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;"
        );

        return $response;
    }
}
```

**Files to modify**:
- `app/Http/Middleware/SecurityHeaders.php` (create)
- `app/Http/Kernel.php` (add to $middleware)

**Time estimate**: 1 hour (including CSP testing)
**Assigned to**: Backend Developer
**Priority**: 🔴 P0 - Critical

---

### 3. No Comprehensive Testing
**Problem**: Limited test coverage, no unit tests for critical healthcare logic

**Impact**: 🟠 **HIGH** - Bugs in healthcare logic could harm patients

**Solution**: See [Testing Strategy](#testing-strategy) section below

**Time estimate**: 2-3 weeks
**Assigned to**: Full Team
**Priority**: 🟠 P1 - High

---

## 🟠 HIGH PRIORITY IMPROVEMENTS

### 4. Implement Repository Pattern for Database Queries

**Problem**: Controllers directly use Eloquent models, making code hard to test and maintain.

**Why Repository Pattern?**
- ✅ Separates business logic from data access
- ✅ Makes testing easier (mock repositories instead of database)
- ✅ Centralizes query logic (no duplicate queries)
- ✅ Easier to switch databases if needed
- ✅ Cleaner controllers (follows Single Responsibility Principle)

**Implementation**:

#### Step 1: Create Repository Interface
```php
// app/Repositories/Contracts/PatientRepositoryInterface.php
<?php

namespace App\Repositories\Contracts;

use App\Models\Patient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PatientRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 20): LengthAwarePaginator;
    public function find(int $id): ?Patient;
    public function create(array $data): Patient;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function search(string $term): Collection;
    public function withActivePrenatalRecords(): Collection;
    public function findByFormattedId(string $formattedId): ?Patient;
}
```

#### Step 2: Create Repository Implementation
```php
// app/Repositories/PatientRepository.php
<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Repositories\Contracts\PatientRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PatientRepository implements PatientRepositoryInterface
{
    protected $model;

    public function __construct(Patient $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->with('activePrenatalRecord')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?Patient
    {
        return $this->model->find($id);
    }

    public function create(array $data): Patient
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $patient = $this->find($id);
        return $patient ? $patient->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $patient = $this->find($id);
        return $patient ? $patient->delete() : false;
    }

    public function search(string $term): Collection
    {
        return $this->model->where(function($q) use ($term) {
            $q->where('first_name', 'LIKE', "%{$term}%")
              ->orWhere('last_name', 'LIKE', "%{$term}%")
              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"])
              ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%");
        })->get();
    }

    public function withActivePrenatalRecords(): Collection
    {
        return $this->model->whereHas('prenatalRecords', function($query) {
            $query->where('is_active', true)
                  ->where('status', '!=', 'completed');
        })->with(['prenatalRecords' => function($query) {
            $query->where('is_active', true)
                  ->where('status', '!=', 'completed')
                  ->latest();
        }])->get();
    }

    public function findByFormattedId(string $formattedId): ?Patient
    {
        return $this->model->where('formatted_patient_id', $formattedId)->first();
    }

    /**
     * Search patients with pagination
     */
    public function searchPaginated(string $term, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->where(function($q) use ($term) {
            $q->where('first_name', 'LIKE', "%{$term}%")
              ->orWhere('last_name', 'LIKE', "%{$term}%")
              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"])
              ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%");
        })
        ->with('activePrenatalRecord')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
    }
}
```

#### Step 3: Bind Repository in Service Provider
```php
// app/Providers/RepositoryServiceProvider.php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\PatientRepositoryInterface;
use App\Repositories\PatientRepository;
use App\Repositories\Contracts\PrenatalRecordRepositoryInterface;
use App\Repositories\PrenatalRecordRepository;
use App\Repositories\Contracts\ChildRecordRepositoryInterface;
use App\Repositories\ChildRecordRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(PatientRepositoryInterface::class, PatientRepository::class);
        $this->app->bind(PrenatalRecordRepositoryInterface::class, PrenatalRecordRepository::class);
        $this->app->bind(ChildRecordRepositoryInterface::class, ChildRecordRepository::class);
        // Add more repository bindings here
    }
}
```

Don't forget to register in `config/app.php`:
```php
'providers' => [
    // ...
    App\Providers\RepositoryServiceProvider::class,
],
```

#### Step 4: Update Controller to Use Repository
```php
// app/Http/Controllers/PatientController.php
<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\PatientRepositoryInterface;
use Illuminate\Http\Request;

class PatientController extends BaseController
{
    protected $patientRepository;

    public function __construct(PatientRepositoryInterface $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    public function index(Request $request)
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        // Clean controller - all query logic in repository
        $patients = $request->filled('search')
            ? $this->patientRepository->searchPaginated($request->search)
            : $this->patientRepository->paginate(20);

        return view($this->roleView('patients.index'), compact('patients'));
    }

    public function store(Request $request)
    {
        // Validation logic...

        $patient = $this->patientRepository->create($request->validated());

        return redirect()->route($this->roleRoute('patients.index'))
            ->with('success', 'Patient registered successfully!');
    }

    public function update(Request $request, $id)
    {
        // Validation logic...

        $this->patientRepository->update($id, $request->validated());

        return redirect()->route($this->roleRoute('patients.index'))
            ->with('success', 'Patient updated successfully!');
    }
}
```

**Files to create**:
- `app/Repositories/Contracts/PatientRepositoryInterface.php`
- `app/Repositories/PatientRepository.php`
- `app/Repositories/Contracts/PrenatalRecordRepositoryInterface.php`
- `app/Repositories/PrenatalRecordRepository.php`
- `app/Repositories/Contracts/ChildRecordRepositoryInterface.php`
- `app/Repositories/ChildRecordRepository.php`
- `app/Providers/RepositoryServiceProvider.php`

**Files to modify**:
- `app/Http/Controllers/PatientController.php`
- `app/Http/Controllers/PrenatalRecordController.php`
- `app/Http/Controllers/ChildRecordController.php`
- `config/app.php`

**Time estimate**: 1-2 weeks
**Assigned to**: Backend Developer
**Priority**: 🟠 P1 - High

---

### 5. Refactor Large Controllers

**Problem**: Some controllers exceed 600 lines (UserController: 620 lines, PrenatalCheckupController: 826 lines)

**Impact**: Hard to maintain, test, and understand

**Solution**: Break down into smaller, focused classes

#### Example: PrenatalCheckupController Refactoring

**Before** (826 lines in one file):
```
PrenatalCheckupController
├── index()
├── create()
├── store()
├── show()
├── edit()
├── update()
├── destroy()
├── markCompleted()
├── markAsMissed()
├── rescheduleMissed()
├── checkTodaysMissed()
├── updateSchedule()
└── ... more methods
```

**After** (split into focused classes):
```
Controllers/PrenatalCheckup/
├── PrenatalCheckupController.php (CRUD operations - 200 lines)
├── PrenatalCheckupStatusController.php (status updates - 150 lines)
├── PrenatalCheckupScheduleController.php (scheduling logic - 200 lines)
└── PrenatalCheckupSearchController.php (search/filter - 100 lines)
```

**Implementation**:
```php
// app/Http/Controllers/PrenatalCheckup/PrenatalCheckupController.php
<?php

namespace App\Http\Controllers\PrenatalCheckup;

use App\Http\Controllers\BaseController;
use App\Repositories\Contracts\PrenatalCheckupRepositoryInterface;

class PrenatalCheckupController extends BaseController
{
    protected $checkupRepository;

    public function __construct(PrenatalCheckupRepositoryInterface $checkupRepository)
    {
        $this->checkupRepository = $checkupRepository;
    }

    public function index(Request $request)
    {
        // Basic CRUD index only
    }

    public function create()
    {
        // Basic create form
    }

    public function store(Request $request)
    {
        // Basic store logic
    }

    public function show($id)
    {
        // Basic show logic
    }

    public function edit($id)
    {
        // Basic edit form
    }

    public function update(Request $request, $id)
    {
        // Basic update logic
    }

    public function destroy($id)
    {
        // Basic delete logic
    }
}

// app/Http/Controllers/PrenatalCheckup/PrenatalCheckupStatusController.php
<?php

namespace App\Http\Controllers\PrenatalCheckup;

use App\Http\Controllers\BaseController;

class PrenatalCheckupStatusController extends BaseController
{
    public function markCompleted($id)
    {
        // Mark as completed logic
    }

    public function markAsMissed(Request $request, $id)
    {
        // Mark as missed logic
    }

    public function rescheduleMissed(Request $request, $id)
    {
        // Reschedule logic
    }
}

// app/Http/Controllers/PrenatalCheckup/PrenatalCheckupScheduleController.php
<?php

namespace App\Http\Controllers\PrenatalCheckup;

use App\Http\Controllers\BaseController;

class PrenatalCheckupScheduleController extends BaseController
{
    public function updateSchedule(Request $request, $id)
    {
        // Schedule update logic
    }

    public function checkTodaysMissed()
    {
        // Auto-check missed checkups
    }
}
```

**Update Routes**:
```php
// routes/web.php
Route::prefix('midwife/prenatalcheckup')
    ->middleware(['auth', 'role:midwife'])
    ->name('midwife.prenatalcheckup.')
    ->group(function () {
        // Basic CRUD
        Route::resource('/', PrenatalCheckup\PrenatalCheckupController::class);

        // Status management
        Route::post('{id}/complete', [PrenatalCheckup\PrenatalCheckupStatusController::class, 'markCompleted'])
            ->name('complete');
        Route::post('{id}/mark-missed', [PrenatalCheckup\PrenatalCheckupStatusController::class, 'markAsMissed'])
            ->name('mark-missed');
        Route::post('{id}/reschedule', [PrenatalCheckup\PrenatalCheckupStatusController::class, 'rescheduleMissed'])
            ->name('reschedule');

        // Schedule management
        Route::put('{id}/schedule', [PrenatalCheckup\PrenatalCheckupScheduleController::class, 'updateSchedule'])
            ->name('schedule');
    });
```

**Files to create**:
- `app/Http/Controllers/PrenatalCheckup/` (directory)
- `app/Http/Controllers/PrenatalCheckup/PrenatalCheckupController.php`
- `app/Http/Controllers/PrenatalCheckup/PrenatalCheckupStatusController.php`
- `app/Http/Controllers/PrenatalCheckup/PrenatalCheckupScheduleController.php`
- `app/Http/Controllers/PrenatalCheckup/PrenatalCheckupSearchController.php`

**Time estimate**: 1 week
**Assigned to**: Backend Developer
**Priority**: 🟠 P1 - High

---

### 6. Complete View Consolidation

**Problem**: Still have duplicate midwife/bhw views for prenatal records and child records

**Impact**: Maintenance burden, code duplication

**Solution**: Follow the pattern established for patients module

**Tasks**:
- [ ] Consolidate prenatal record views
- [ ] Consolidate child record views
- [ ] Consolidate prenatal checkup views (if BHW has access)
- [ ] Delete old midwife/ and bhw/ view folders
- [ ] Test thoroughly with both roles

**Files affected**: ~40-50 view files

**Time estimate**: 4-6 hours
**Assigned to**: Frontend Developer
**Priority**: 🟠 P1 - High

**Follow the guide**: `REFACTORING_GUIDE.md`

---

## 🟡 MEDIUM PRIORITY ENHANCEMENTS

### 7. Add Query Caching

**Problem**: Dashboard and frequently accessed pages run the same queries repeatedly

**Impact**: 🟡 **MEDIUM** - Performance degradation with more users

**Solution**: Implement query result caching

```php
// app/Repositories/PatientRepository.php

use Illuminate\Support\Facades\Cache;

class PatientRepository implements PatientRepositoryInterface
{
    public function dashboardStats(): array
    {
        return Cache::remember('dashboard_stats', 600, function () {
            return [
                'total_patients' => $this->model->count(),
                'active_pregnancies' => $this->model->whereHas('prenatalRecords', function($q) {
                    $q->where('is_active', true)->where('status', '!=', 'completed');
                })->count(),
                'high_risk_patients' => $this->model->whereHas('prenatalRecords', function($q) {
                    $q->where('status', 'high-risk');
                })->count(),
                'children_due_vaccination' => ChildRecord::whereHas('immunizations', function($q) {
                    $q->where('status', 'upcoming')
                      ->whereDate('schedule_date', '<=', now()->addDays(7));
                })->count(),
            ];
        });
    }

    // Clear cache when data changes
    public function create(array $data): Patient
    {
        $patient = $this->model->create($data);
        Cache::forget('dashboard_stats');
        return $patient;
    }
}
```

**Implementation checklist**:
- [ ] Cache dashboard statistics (10 minute TTL)
- [ ] Cache patient lists (5 minute TTL)
- [ ] Cache dropdown options (1 hour TTL)
- [ ] Implement cache tags for easier invalidation
- [ ] Add cache clearing events when data changes

**Time estimate**: 3-4 days
**Assigned to**: Backend Developer
**Priority**: 🟡 P2 - Medium

---

### 8. Implement Event/Listener Pattern

**Problem**: Currently using Observers, but some actions need more flexibility

**Impact**: Limited ability to queue notifications, hard to add new listeners

**Solution**: Migrate to Laravel Events

```php
// app/Events/PatientRegistered.php
<?php

namespace App\Events;

use App\Models\Patient;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PatientRegistered
{
    use Dispatchable, SerializesModels;

    public $patient;

    public function __construct(Patient $patient)
    {
        $this->patient = $patient;
    }
}

// app/Listeners/SendPatientRegistrationNotification.php
<?php

namespace App\Listeners;

use App\Events\PatientRegistered;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPatientRegistrationNotification implements ShouldQueue
{
    public function handle(PatientRegistered $event)
    {
        NotificationService::sendNewPatientNotification($event->patient);
    }
}

// app/Providers/EventServiceProvider.php
protected $listen = [
    PatientRegistered::class => [
        SendPatientRegistrationNotification::class,
        LogPatientActivity::class, // Easy to add more listeners!
    ],
    PrenatalCheckupScheduled::class => [
        SendCheckupConfirmation::class,
        UpdatePrenatalRecordStatus::class,
    ],
];

// In Controller or Service
event(new PatientRegistered($patient));
```

**Benefits**:
- ✅ Queued notifications (don't block user requests)
- ✅ Multiple listeners for same event
- ✅ Easier testing (mock events)
- ✅ Better separation of concerns

**Time estimate**: 1 week
**Assigned to**: Backend Developer
**Priority**: 🟡 P2 - Medium

---

### 9. Add Comprehensive Logging

**Problem**: Limited logging for debugging and auditing

**Impact**: Hard to debug production issues, no audit trail

**Solution**: Implement structured logging

```php
// app/Services/AuditLogService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public static function log(string $action, string $model, $modelId, array $changes = [])
    {
        Log::channel('audit')->info('User action', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'System',
            'user_role' => Auth::user()->role ?? 'System',
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}

// Usage in Controller
AuditLogService::log('created', 'Patient', $patient->id, [
    'name' => $patient->name,
    'age' => $patient->age,
]);

AuditLogService::log('updated', 'PrenatalRecord', $record->id, [
    'old_status' => $oldStatus,
    'new_status' => $newStatus,
]);
```

**Configure log channels**:
```php
// config/logging.php
'channels' => [
    'audit' => [
        'driver' => 'daily',
        'path' => storage_path('logs/audit.log'),
        'level' => 'info',
        'days' => 90, // Keep audit logs for 90 days
    ],
    'healthcare' => [
        'driver' => 'daily',
        'path' => storage_path('logs/healthcare.log'),
        'level' => 'info',
        'days' => 30,
    ],
];
```

**Time estimate**: 2-3 days
**Assigned to**: Backend Developer
**Priority**: 🟡 P2 - Medium

---

### 10. Optimize N+1 Queries

**Problem**: Potential N+1 queries in patient listing, dashboard, reports

**Impact**: Slow page loads with more data

**Solution**: Use eager loading consistently

```php
// Before (N+1 query problem)
$patients = Patient::all(); // 1 query
foreach ($patients as $patient) {
    echo $patient->prenatalRecords->count(); // N queries!
}

// After (Eager loading)
$patients = Patient::withCount('prenatalRecords') // 1 query
    ->with(['prenatalRecords' => function($query) {
        $query->where('is_active', true)->latest();
    }])
    ->get();

foreach ($patients as $patient) {
    echo $patient->prenatal_records_count; // No additional queries
}
```

**Use Laravel Debugbar to find N+1 queries**:
```bash
composer require barryvdh/laravel-debugbar --dev
```

**Checklist**:
- [ ] Analyze all list pages with Debugbar
- [ ] Add eager loading where needed
- [ ] Use `withCount()` for counts
- [ ] Use `with()` for relationships
- [ ] Test with large datasets (1000+ records)

**Time estimate**: 2 days
**Assigned to**: Backend Developer
**Priority**: 🟡 P2 - Medium

---

## 📅 LONG-TERM ARCHITECTURE IMPROVEMENTS

### 11. Implement API Versioning

**Problem**: No API versioning strategy for future mobile app or integrations

**Impact**: Breaking changes will affect clients

**Solution**: Implement versioned API

```php
// routes/api.php
Route::prefix('api/v1')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::apiResource('patients', Api\V1\PatientController::class);
        Route::apiResource('prenatal-records', Api\V1\PrenatalRecordController::class);
    });
});

Route::prefix('api/v2')->group(function () {
    // Future version with breaking changes
});
```

**Time estimate**: 1 week
**Priority**: 🔵 P3 - Low (for future)

---

### 12. Add Two-Factor Authentication (2FA)

**Problem**: Only password-based authentication for midwives accessing sensitive data

**Impact**: Vulnerable to password theft

**Solution**: Implement 2FA using Laravel Fortify or custom implementation

**Time estimate**: 1 week
**Priority**: 🔵 P3 - Low (nice to have)

---

### 13. Implement Full-Text Search

**Problem**: Basic LIKE queries for search, slow with large datasets

**Impact**: Poor search performance

**Solution**: Use Laravel Scout with database driver or Meilisearch

```bash
composer require laravel/scout
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
```

```php
// app/Models/Patient.php
use Laravel\Scout\Searchable;

class Patient extends Model
{
    use Searchable;

    public function toSearchableArray()
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'formatted_patient_id' => $this->formatted_patient_id,
            'address' => $this->address,
        ];
    }
}

// In Controller
$patients = Patient::search($request->search)->paginate();
```

**Time estimate**: 2-3 days
**Priority**: 🔵 P3 - Low (for scalability)

---

## 🧪 TESTING STRATEGY

### Test Coverage Goals
- **Unit Tests**: 80%+ coverage for business logic
- **Feature Tests**: 100% coverage for critical workflows
- **Integration Tests**: API endpoints and database operations

### Priority Test Areas

#### 1. Critical Healthcare Logic Tests
```php
// tests/Unit/Models/PrenatalRecordTest.php
<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\PrenatalRecord;
use Carbon\Carbon;

class PrenatalRecordTest extends TestCase
{
    /** @test */
    public function it_calculates_gestational_age_correctly()
    {
        $record = new PrenatalRecord([
            'last_menstrual_period' => Carbon::now()->subWeeks(12)->subDays(3),
        ]);

        $this->assertEquals('12 weeks 3 days', $record->getCurrentGestationalAge());
    }

    /** @test */
    public function it_determines_correct_trimester()
    {
        $firstTrimester = new PrenatalRecord([
            'last_menstrual_period' => Carbon::now()->subWeeks(10),
        ]);
        $this->assertEquals(1, $firstTrimester->getCurrentTrimester());

        $secondTrimester = new PrenatalRecord([
            'last_menstrual_period' => Carbon::now()->subWeeks(20),
        ]);
        $this->assertEquals(2, $secondTrimester->getCurrentTrimester());

        $thirdTrimester = new PrenatalRecord([
            'last_menstrual_period' => Carbon::now()->subWeeks(35),
        ]);
        $this->assertEquals(3, $thirdTrimester->getCurrentTrimester());
    }

    /** @test */
    public function it_identifies_overdue_pregnancies()
    {
        $overdue = new PrenatalRecord([
            'expected_due_date' => Carbon::now()->subDays(5),
        ]);
        $this->assertTrue($overdue->is_overdue);

        $notOverdue = new PrenatalRecord([
            'expected_due_date' => Carbon::now()->addDays(5),
        ]);
        $this->assertFalse($notOverdue->is_overdue);
    }
}
```

#### 2. Feature Tests for Workflows
```php
// tests/Feature/PrenatalCheckupWorkflowTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Patient;
use App\Models\PrenatalRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PrenatalCheckupWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function midwife_can_schedule_prenatal_checkup()
    {
        $midwife = User::factory()->create(['role' => 'midwife']);
        $patient = Patient::factory()->create();
        $record = PrenatalRecord::factory()->create(['patient_id' => $patient->id]);

        $response = $this->actingAs($midwife)
            ->post(route('midwife.prenatalcheckup.store'), [
                'patient_id' => $patient->id,
                'prenatal_record_id' => $record->id,
                'checkup_date' => now()->addWeek(),
                'checkup_time' => '09:00',
            ]);

        $response->assertRedirect(route('midwife.prenatalcheckup.index'));
        $this->assertDatabaseHas('prenatal_checkups', [
            'patient_id' => $patient->id,
            'status' => 'upcoming',
        ]);
    }

    /** @test */
    public function bhw_cannot_access_vaccine_inventory()
    {
        $bhw = User::factory()->create(['role' => 'bhw']);

        $response = $this->actingAs($bhw)
            ->get(route('midwife.vaccines.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function system_auto_marks_checkups_as_missed_after_5pm()
    {
        Carbon::setTestNow(Carbon::today()->setHour(17)); // 5 PM

        $checkup = PrenatalCheckup::factory()->create([
            'checkup_date' => Carbon::today(),
            'status' => 'upcoming',
        ]);

        // Trigger the auto-check (this would be called by a scheduled task)
        $this->artisan('checkups:check-missed');

        $checkup->refresh();
        $this->assertEquals('missed', $checkup->status);
    }
}
```

#### 3. Repository Tests
```php
// tests/Unit/Repositories/PatientRepositoryTest.php
<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\PatientRepository;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PatientRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new PatientRepository(new Patient());
    }

    /** @test */
    public function it_can_search_patients_by_name()
    {
        Patient::factory()->create(['first_name' => 'Maria', 'last_name' => 'Santos']);
        Patient::factory()->create(['first_name' => 'Juan', 'last_name' => 'Cruz']);

        $results = $this->repository->search('Maria');

        $this->assertEquals(1, $results->count());
        $this->assertEquals('Maria', $results->first()->first_name);
    }

    /** @test */
    public function it_returns_only_patients_with_active_prenatal_records()
    {
        $activePatient = Patient::factory()
            ->has(PrenatalRecord::factory()->state(['is_active' => true, 'status' => 'normal']))
            ->create();

        $inactivePatient = Patient::factory()
            ->has(PrenatalRecord::factory()->state(['is_active' => false]))
            ->create();

        $results = $this->repository->withActivePrenatalRecords();

        $this->assertEquals(1, $results->count());
        $this->assertEquals($activePatient->id, $results->first()->id);
    }
}
```

**Testing Checklist**:
- [ ] Write unit tests for all models
- [ ] Write feature tests for all CRUD operations
- [ ] Write feature tests for role-based access
- [ ] Write tests for business logic (gestational age, vaccine schedules, etc.)
- [ ] Write tests for notifications
- [ ] Write tests for SMS integration
- [ ] Set up CI/CD pipeline to run tests automatically

**Time estimate**: 3-4 weeks
**Priority**: 🟠 P1 - High

---

## 🔒 SECURITY HARDENING

### Tasks

1. **Input Sanitization**
   - [ ] Review all user inputs for XSS vulnerabilities
   - [ ] Implement HTML Purifier for rich text fields
   - [ ] Add CSRF protection to AJAX requests

2. **Database Security**
   - [ ] Review all raw queries for SQL injection risks
   - [ ] Implement prepared statements consistently
   - [ ] Add database connection encryption

3. **File Upload Security** (if applicable)
   - [ ] Validate file types server-side
   - [ ] Scan uploaded files for malware
   - [ ] Store uploads outside public directory

4. **Rate Limiting Enhancement**
   - [ ] Add stricter rate limits for sensitive endpoints
   - [ ] Implement exponential backoff for repeated failures
   - [ ] Add IP-based blocking for suspicious activity

5. **Sensitive Data Protection**
   - [ ] Review logs for PHI leakage
   - [ ] Implement field-level encryption for sensitive data
   - [ ] Add data masking in non-production environments

**Time estimate**: 2 weeks
**Priority**: 🟠 P1 - High

---

## ⚡ PERFORMANCE OPTIMIZATION

### Database Optimization

1. **Add Missing Indexes**
```sql
-- Analyze slow queries
EXPLAIN SELECT * FROM prenatal_checkups WHERE status = 'upcoming' AND checkup_date < NOW();

-- Add composite indexes
ALTER TABLE prenatal_checkups ADD INDEX idx_status_date (status, checkup_date);
ALTER TABLE patients ADD INDEX idx_name (first_name, last_name);
ALTER TABLE prenatal_records ADD INDEX idx_patient_active (patient_id, is_active, status);
```

2. **Optimize Heavy Queries**
   - [ ] Identify slow queries using Laravel Telescope
   - [ ] Add database indexes for frequently queried columns
   - [ ] Use database views for complex reports
   - [ ] Implement query result caching

3. **Database Connection Pooling**
   - [ ] Configure database connection pool
   - [ ] Monitor connection usage
   - [ ] Optimize long-running queries

### Application Optimization

1. **Asset Optimization**
   - [ ] Minify CSS and JavaScript
   - [ ] Implement lazy loading for images
   - [ ] Use CDN for static assets
   - [ ] Enable browser caching

2. **Response Caching**
```php
// routes/web.php
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('cache.response:600'); // Cache for 10 minutes
```

3. **Queue Long-Running Tasks**
   - [ ] Move SMS sending to queues
   - [ ] Queue backup operations
   - [ ] Queue report generation

**Time estimate**: 1-2 weeks
**Priority**: 🟡 P2 - Medium

---

## 📝 CODE QUALITY IMPROVEMENTS

### 1. Add PHPDoc Comments
```php
/**
 * Calculate gestational age from last menstrual period
 *
 * @param \Carbon\Carbon $lmpDate Last menstrual period date
 * @param \Carbon\Carbon|null $currentDate Current date for calculation
 * @return string|null Gestational age in format "X weeks Y days"
 */
private function calculateGestationalAgeFromLMP($lmpDate, $currentDate = null)
{
    // Implementation
}
```

### 2. Extract Traits for Common Functionality
```php
// app/Traits/HasFormattedId.php
<?php

namespace App\Traits;

trait HasFormattedId
{
    protected static function bootHasFormattedId()
    {
        static::creating(function ($model) {
            if (empty($model->formatted_id)) {
                $model->formatted_id = static::generateFormattedId();
            }
        });
    }

    abstract protected static function generateFormattedId(): string;
}

// Use in models
class Patient extends Model
{
    use HasFormattedId;

    protected static function generateFormattedId(): string
    {
        $last = static::withTrashed()->orderByDesc('id')->first();
        return 'PT-' . str_pad(($last ? $last->id + 1 : 1), 3, '0', STR_PAD_LEFT);
    }
}
```

### 3. Implement Consistent Error Handling
```php
// app/Exceptions/Handler.php
public function render($request, Throwable $exception)
{
    if ($exception instanceof ModelNotFoundException) {
        return response()->json([
            'error' => 'Resource not found',
            'message' => 'The requested resource does not exist.',
        ], 404);
    }

    if ($exception instanceof ValidationException) {
        return response()->json([
            'error' => 'Validation failed',
            'errors' => $exception->errors(),
        ], 422);
    }

    return parent::render($request, $exception);
}
```

**Time estimate**: 1 week
**Priority**: 🟡 P2 - Medium

---

## 📊 PRIORITY MATRIX

| Priority | Task | Impact | Effort | Timeline |
|----------|------|--------|--------|----------|
| 🔴 P0 | HTTPS Enforcement | Critical | Low | Immediate |
| 🔴 P0 | Security Headers | Critical | Low | Immediate |
| 🟠 P1 | Comprehensive Testing | High | High | 3-4 weeks |
| 🟠 P1 | Repository Pattern | High | Medium | 1-2 weeks |
| 🟠 P1 | Refactor Large Controllers | High | Medium | 1 week |
| 🟠 P1 | Complete View Consolidation | High | Low | 4-6 hours |
| 🟠 P1 | Security Hardening | High | Medium | 2 weeks |
| 🟡 P2 | Query Caching | Medium | Low | 3-4 days |
| 🟡 P2 | Event/Listener Pattern | Medium | Medium | 1 week |
| 🟡 P2 | Comprehensive Logging | Medium | Low | 2-3 days |
| 🟡 P2 | Optimize N+1 Queries | Medium | Low | 2 days |
| 🟡 P2 | Performance Optimization | Medium | Medium | 1-2 weeks |
| 🟡 P2 | Code Quality | Medium | Medium | 1 week |
| 🔵 P3 | API Versioning | Low | Medium | 1 week |
| 🔵 P3 | Two-Factor Auth | Low | Medium | 1 week |
| 🔵 P3 | Full-Text Search | Low | Low | 2-3 days |

---

## 🎯 RECOMMENDED SPRINT PLAN

### Sprint 1 (Week 1-2): Critical Security & Foundation
- [ ] HTTPS Enforcement
- [ ] Security Headers
- [ ] Begin Repository Pattern implementation
- [ ] Complete view consolidation

### Sprint 2 (Week 3-4): Testing & Refactoring
- [ ] Write unit tests for models
- [ ] Write feature tests for critical workflows
- [ ] Refactor PrenatalCheckupController
- [ ] Refactor UserController

### Sprint 3 (Week 5-6): Performance & Quality
- [ ] Implement query caching
- [ ] Optimize N+1 queries
- [ ] Add comprehensive logging
- [ ] Implement Event/Listener pattern

### Sprint 4 (Week 7-8): Polish & Documentation
- [ ] Security hardening
- [ ] Performance optimization
- [ ] Add PHPDoc comments
- [ ] Update documentation

---

## 📈 METRICS TO TRACK

1. **Code Quality**
   - Test coverage percentage
   - Number of lines per controller (target: <300)
   - PHPStan/Psalm error count (target: 0)

2. **Performance**
   - Average page load time (target: <2s)
   - Database query count per page (target: <50)
   - Cache hit ratio (target: >80%)

3. **Security**
   - Number of security vulnerabilities (target: 0)
   - Failed login attempts per day
   - HTTPS adoption rate (target: 100%)

4. **User Experience**
   - Average response time for API calls (target: <500ms)
   - Error rate (target: <1%)
   - Uptime (target: 99.9%)

---

## 🔗 USEFUL RESOURCES

- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [Repository Pattern Guide](https://www.twilio.com/blog/repository-pattern-in-laravel-application)
- [Laravel Testing Documentation](https://laravel.com/docs/11.x/testing)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)

---

**Document Version**: 1.0
**Last Updated**: November 2025
**Status**: Ready for Implementation
