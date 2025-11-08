# Controller Refactoring Analysis - Service Layer Extraction

## Overview

This document identifies controllers that contain business logic that should be refactored into Service classes following the Repository/Service pattern.

---

## ✅ Controllers Already Using Services

These controllers are already properly structured with Service classes:

| Controller | Service | Status |
|------------|---------|--------|
| PatientController | PatientService | ✅ Implemented |
| PrenatalRecordController | PrenatalRecordService | ✅ Implemented |
| PrenatalCheckupController | PrenatalCheckupService | ✅ Implemented |
| ImmunizationController | ImmunizationService | ✅ Implemented |
| ChildRecordController | ChildRecordService | ✅ Implemented |
| CloudBackupController | DatabaseBackupService, GoogleDriveService | ✅ Implemented |
| NotificationController | NotificationService | ✅ Implemented |
| SmsLogController | SmsService | ✅ Implemented |

---

## 🔴 Controllers Needing Service Layer Extraction

### 1. **VaccineController** (Priority: HIGH)

**File:** `app/Http/Controllers/VaccineController.php`
**Lines:** ~290 lines
**Issues:**
- ❌ Business logic directly in controller
- ❌ Database transactions in controller
- ❌ Notification sending logic in controller
- ❌ Cache management in controller

**Business Logic to Extract:**

```php
// Current (in Controller):
public function store(Request $request)
{
    DB::beginTransaction();

    $vaccine = Vaccine::create([
        'name' => $validated['name'],
        'category' => $validated['category'],
        // ... more fields
    ]);

    // Send notification to all healthcare workers
    $workers = User::whereIn('role', ['midwife', 'bhw', 'admin'])->get();
    foreach ($workers as $worker) {
        $worker->notify(new HealthcareNotification(
            'New Vaccine Added',
            "New vaccine '{$vaccine->name}' has been added to the system.",
            'info',
            route('midwife.vaccines.index')
        ));

        Cache::forget("unread_notifications_count_{$worker->id}");
        Cache::forget("recent_notifications_{$worker->id}");
    }

    DB::commit();
}
```

**Should Be (with Service):**

```php
// Controller:
public function store(Request $request)
{
    $validated = $request->validate(...);

    $vaccine = $this->vaccineService->createVaccine($validated);

    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => "Vaccine '{$vaccine->name}' has been created successfully!"
        ]);
    }

    return redirect()->route('midwife.vaccines.index')
        ->with('success', "Vaccine '{$vaccine->name}' has been created successfully!");
}

// Service:
class VaccineService
{
    public function createVaccine(array $data): Vaccine
    {
        return DB::transaction(function () use ($data) {
            $vaccine = Vaccine::create($data);

            $this->notifyHealthcareWorkers(
                'New Vaccine Added',
                "New vaccine '{$vaccine->name}' has been added to the system.",
                'info',
                route('midwife.vaccines.index')
            );

            return $vaccine;
        });
    }

    protected function notifyHealthcareWorkers($title, $message, $type, $url)
    {
        $workers = User::whereIn('role', ['midwife', 'bhw', 'admin'])->get();

        foreach ($workers as $worker) {
            $worker->notify(new HealthcareNotification($title, $message, $type, $url));
            Cache::forget("unread_notifications_count_{$worker->id}");
            Cache::forget("recent_notifications_{$worker->id}");
        }
    }
}
```

**Methods to Extract:**
- `createVaccine()` - Create vaccine with notifications
- `updateVaccine()` - Update vaccine with notifications
- `updateStock()` - Handle stock transactions
- `getVaccineStats()` - Get vaccine statistics
- `notifyHealthcareWorkers()` - Send notifications (helper method)

**Estimated Effort:** 2-3 hours

---

### 2. **UserController** (Priority: MEDIUM)

**File:** `app/Http/Controllers/UserController.php`
**Lines:** ~300 lines
**Issues:**
- ❌ Password hashing logic in controller
- ❌ User creation/update logic in controller
- ❌ Authorization checks duplicated across methods

**Business Logic to Extract:**

```php
// Current:
public function store(Request $request)
{
    $validated = $request->validate(
        User::validationRules(),
        User::validationMessages()
    );

    $validated['password'] = Hash::make($validated['password']);
    $newUser = User::create($validated);
}

public function resetPassword(Request $request, User $user)
{
    $newPassword = Str::random(10);
    $user->password = Hash::make($newPassword);
    $user->save();
}
```

**Should Be:**

```php
// Controller:
public function store(Request $request)
{
    $validated = $request->validate(...);
    $user = $this->userService->createUser($validated);

    return response()->json([
        'success' => true,
        'message' => 'User created successfully!'
    ]);
}

// Service:
class UserService
{
    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    public function updateUser(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return $user->fresh();
    }

    public function resetPassword(User $user): string
    {
        $newPassword = Str::random(10);
        $user->password = Hash::make($newPassword);
        $user->save();

        return $newPassword;
    }

    public function toggleUserStatus(User $user): User
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return $user;
    }
}
```

**Methods to Extract:**
- `createUser()` - Create user with hashed password
- `updateUser()` - Update user data
- `resetPassword()` - Reset user password
- `toggleUserStatus()` - Activate/deactivate user
- `getUserStats()` - Get user statistics

**Estimated Effort:** 2 hours

---

### 3. **DashboardController** (Priority: LOW - Read-only)

**File:** `app/Http/Controllers/DashboardController.php`
**Lines:** ~439 lines
**Issues:**
- ❌ Complex statistical queries directly in controller
- ❌ Data aggregation logic in controller
- ⚠️ Mostly read-only operations (less critical)

**Business Logic to Extract:**

```php
// Current:
public function index()
{
    $stats = [
        'total_patients' => Patient::count(),
        'active_prenatal_records' => PrenatalRecord::where('is_active', true)->count(),
        'checkups_this_month' => PrenatalCheckup::whereMonth('checkup_date', $currentMonth)
                                               ->whereYear('checkup_date', $currentYear)
                                               ->count(),
        // ... many more stats
    ];

    $checkupsPerMonth = [];
    for ($i = 11; $i >= 0; $i--) {
        $date = Carbon::now()->subMonths($i);
        $checkupsPerMonth[] = PrenatalCheckup::whereMonth('checkup_date', $date->month)
                                           ->whereYear('checkup_date', $date->year)
                                           ->count();
    }

    // ... lots more data aggregation
}
```

**Should Be:**

```php
// Controller:
public function index()
{
    $stats = $this->dashboardService->getDashboardStats();
    $charts = $this->dashboardService->getChartData();
    $recentActivity = $this->dashboardService->getRecentActivity();

    return view('midwife.dashboard', compact('stats', 'charts', 'recentActivity'));
}

// Service:
class DashboardService
{
    public function getDashboardStats(): array
    {
        $currentDate = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        return [
            'total_patients' => Patient::count(),
            'active_prenatal_records' => PrenatalRecord::where('is_active', true)->count(),
            'checkups_this_month' => $this->getCheckupsForMonth($currentDate),
            'total_children' => ChildRecord::count(),
            'upcoming_appointments' => $this->getUpcomingAppointments(),
            'checkups_change' => $this->getMonthOverMonthChange($currentDate, $lastMonth),
        ];
    }

    public function getChartData(): array
    {
        return [
            'checkups_per_month' => $this->getCheckupsPerMonth(12),
            'registration_trends' => $this->getRegistrationTrends(12),
            'immunization_stats' => $this->getImmunizationStats(),
            'vaccine_usage' => $this->getVaccineUsageData(),
        ];
    }

    protected function getCheckupsForMonth(Carbon $date): int
    {
        return PrenatalCheckup::whereMonth('checkup_date', $date->month)
                             ->whereYear('checkup_date', $date->year)
                             ->count();
    }

    // ... more helper methods
}
```

**Methods to Extract:**
- `getDashboardStats()` - Get all dashboard statistics
- `getChartData()` - Get data for charts
- `getRecentActivity()` - Get recent checkups/activities
- `getImmunizationStats()` - Get immunization coverage
- `getCheckupsPerMonth()` - Calculate checkup trends
- `getRegistrationTrends()` - Calculate registration trends

**Estimated Effort:** 3-4 hours (lots of logic to organize)

---

### 4. **ReportController** (Priority: LOW - Export focused)

**File:** `app/Http/Controllers/ReportController.php`
**Lines:** ~1065 lines (HUGE!)
**Issues:**
- ❌ Massive controller with complex report generation
- ❌ PDF/Excel export logic in controller
- ❌ Data filtering and aggregation in controller
- ⚠️ May benefit from ReportService + ExportService

**Business Logic to Extract:**

Could be split into:
- `ReportService` - Generate report data
- `PdfExportService` - Export to PDF
- `ExcelExportService` - Export to Excel

**Estimated Effort:** 8-10 hours (very large refactoring)

---

### 5. **AppointmentController** (Priority: MEDIUM)

**File:** `app/Http/Controllers/AppointmentController.php`
**Lines:** ~248 lines
**Issues:**
- ❌ Appointment scheduling logic in controller
- ❌ Conflict checking in controller
- ❌ Notification sending in controller

**Should Have:**
- `AppointmentService` with methods:
  - `scheduleAppointment()`
  - `rescheduleAppointment()`
  - `cancelAppointment()`
  - `checkConflicts()`
  - `getAvailableSlots()`

**Estimated Effort:** 2-3 hours

---

## 📊 Refactoring Priority Ranking

| Priority | Controller | Reason | Effort | Impact |
|----------|-----------|---------|---------|---------|
| 1 | VaccineController | Heavy business logic, notifications, transactions | 2-3h | High |
| 2 | UserController | Password management, user lifecycle | 2h | Medium |
| 3 | AppointmentController | Scheduling logic, conflict detection | 2-3h | Medium |
| 4 | DashboardController | Complex stats (but read-only) | 3-4h | Low |
| 5 | ReportController | Huge file, but mostly formatting | 8-10h | Medium |

---

## 🎯 Recommended Action Plan

### Phase 1: Critical Business Logic (Week 1)
1. ✅ Create `VaccineService`
   - Extract vaccine CRUD operations
   - Move notification logic to service
   - Add stock management methods

2. ✅ Create `UserService`
   - Extract user management logic
   - Move password hashing to service
   - Add user lifecycle methods

### Phase 2: Appointments & Scheduling (Week 2)
3. ✅ Create `AppointmentService`
   - Extract appointment scheduling
   - Add conflict detection
   - Move notifications to service

### Phase 3: Reporting & Analytics (Week 3-4)
4. ✅ Create `DashboardService`
   - Extract statistics calculation
   - Move chart data generation
   - Add caching layer

5. ✅ Create `ReportService` + Export Services
   - Split into smaller, focused services
   - Extract data aggregation
   - Separate export logic

---

## 📝 Service Layer Best Practices

### ✅ DO:
- Keep controllers thin (validation + response)
- Put business logic in services
- Use database transactions in services
- Handle notifications in services
- Cache in services, not controllers
- Return domain objects from services

### ❌ DON'T:
- Put database queries in controllers
- Handle transactions in controllers
- Send notifications from controllers
- Do complex calculations in controllers
- Manage cache in controllers

---

## Example Service Structure

```php
<?php

namespace App\Services;

use App\Models\Vaccine;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Notifications\HealthcareNotification;

class VaccineService
{
    /**
     * Create a new vaccine
     */
    public function createVaccine(array $data): Vaccine
    {
        return DB::transaction(function () use ($data) {
            $vaccine = Vaccine::create($data);

            $this->notifyHealthcareWorkers(
                'New Vaccine Added',
                "New vaccine '{$vaccine->name}' has been added to the system.",
                'info',
                route('midwife.vaccines.index')
            );

            return $vaccine;
        });
    }

    /**
     * Update an existing vaccine
     */
    public function updateVaccine(Vaccine $vaccine, array $data): Vaccine
    {
        return DB::transaction(function () use ($vaccine, $data) {
            $vaccine->update($data);

            $this->notifyHealthcareWorkers(
                'Vaccine Updated',
                "Vaccine '{$vaccine->name}' has been updated.",
                'info',
                route('midwife.vaccines.index')
            );

            return $vaccine->fresh();
        });
    }

    /**
     * Update vaccine stock
     */
    public function updateStock(Vaccine $vaccine, int $quantity, string $type, ?string $notes = null): void
    {
        DB::transaction(function () use ($vaccine, $quantity, $type, $notes) {
            if ($type === 'add') {
                $vaccine->increment('current_stock', $quantity);
            } else {
                $vaccine->decrement('current_stock', $quantity);
            }

            // Check if stock is low after update
            if ($vaccine->current_stock <= $vaccine->min_stock) {
                $this->notifyLowStock($vaccine);
            }
        });
    }

    /**
     * Get vaccine statistics
     */
    public function getVaccineStats(): array
    {
        return [
            'total' => Vaccine::count(),
            'in_stock' => Vaccine::whereRaw('current_stock > min_stock')->count(),
            'low_stock' => Vaccine::lowStock()->count(),
            'out_of_stock' => Vaccine::outOfStock()->count(),
        ];
    }

    /**
     * Notify healthcare workers
     */
    protected function notifyHealthcareWorkers(string $title, string $message, string $type, string $url): void
    {
        $workers = User::whereIn('role', ['midwife', 'bhw', 'admin'])->get();

        foreach ($workers as $worker) {
            $worker->notify(new HealthcareNotification($title, $message, $type, $url));

            // Clear notification caches
            Cache::forget("unread_notifications_count_{$worker->id}");
            Cache::forget("recent_notifications_{$worker->id}");
        }
    }

    /**
     * Notify about low stock
     */
    protected function notifyLowStock(Vaccine $vaccine): void
    {
        $this->notifyHealthcareWorkers(
            'Low Vaccine Stock Alert',
            "Vaccine '{$vaccine->name}' is running low on stock. Current: {$vaccine->current_stock}, Minimum: {$vaccine->min_stock}",
            'warning',
            route('midwife.vaccines.index')
        );
    }
}
```

---

## 🔧 Implementation Checklist

For each Service to create:

- [ ] Create Service class in `app/Services/`
- [ ] Extract business logic from controller
- [ ] Add proper method documentation
- [ ] Use database transactions where needed
- [ ] Handle notifications in service
- [ ] Add error handling
- [ ] Write unit tests
- [ ] Update controller to inject service
- [ ] Update controller methods to use service
- [ ] Test all functionality
- [ ] Update documentation

---

**Last Updated:** 2025-11-04
**Status:** Analysis Complete - Ready for Implementation
**Estimated Total Effort:** 17-22 hours
