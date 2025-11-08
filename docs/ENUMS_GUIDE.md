# Enums Usage Guide

## Overview

This application now uses PHP 8.1+ Enums to replace magic strings for status values and roles. This improves code maintainability, type safety, and prevents typos.

## Available Enums

### 1. ImmunizationStatus

**Location:** `app/Enums/ImmunizationStatus.php`

**Values:**
- `UPCOMING` - Immunization is scheduled but not yet done
- `DONE` - Immunization has been completed
- `MISSED` - Immunization was missed

**Usage:**

```php
use App\Enums\ImmunizationStatus;

// Setting status
$immunization->status = ImmunizationStatus::UPCOMING->value;

// Comparing status
if ($immunization->status === ImmunizationStatus::DONE->value) {
    // Do something
}

// Using helper methods
$status = ImmunizationStatus::from($immunization->status);
if ($status->isDone()) {
    // Status is done
}

// Get badge class for UI
$badgeClass = $status->badgeClass(); // Returns 'bg-green-100 text-green-800'

// Get icon
$icon = $status->icon(); // Returns 'fa-check-circle'

// Get all values
$allStatuses = ImmunizationStatus::values(); // ['Upcoming', 'Done', 'Missed']
```

### 2. CheckupStatus

**Location:** `app/Enums/CheckupStatus.php`

**Values:**
- `SCHEDULED` - Checkup is scheduled
- `COMPLETED` - Checkup has been completed
- `MISSED` - Checkup was missed
- `CANCELLED` - Checkup was cancelled

**Usage:**

```php
use App\Enums\CheckupStatus;

// Setting status
$checkup->status = CheckupStatus::SCHEDULED->value;

// Comparing status
if ($checkup->status === CheckupStatus::COMPLETED->value) {
    // Do something
}

// Using helper methods
$status = CheckupStatus::from($checkup->status);
if ($status->isCompleted()) {
    // Status is completed
}

// Get all values
$allStatuses = CheckupStatus::values(); // ['Scheduled', 'Completed', 'Missed', 'Cancelled']
```

### 3. UserRole

**Location:** `app/Enums/UserRole.php`

**Values:**
- `MIDWIFE` - Midwife role
- `BHW` - Barangay Health Worker role
- `ADMIN` - Administrator role

**Usage:**

```php
use App\Enums/UserRole;

// Setting role
$user->role = UserRole::MIDWIFE->value;

// Comparing role
if ($user->role === UserRole::MIDWIFE->value) {
    // User is a midwife
}

// Using helper methods
$role = UserRole::from($user->role);
if ($role->isMidwife()) {
    // User is a midwife
}

// Permission checking
if ($role->can('manage_users')) {
    // User can manage users
}

// Get all values
$allRoles = UserRole::values(); // ['midwife', 'bhw', 'admin']
```

## Benefits of Using Enums

### 1. Type Safety

```php
// ❌ Before (prone to typos)
if ($status === 'Done') { // Could typo as 'done' or 'DONE'
    // ...
}

// ✅ After (IDE autocomplete + type checking)
if ($status === ImmunizationStatus::DONE->value) {
    // ...
}
```

### 2. Centralized Definition

```php
// ❌ Before (magic strings scattered everywhere)
'Upcoming' // Appears in 20+ files

// ✅ After (single source of truth)
ImmunizationStatus::UPCOMING->value // Defined once
```

### 3. Helper Methods

```php
// ❌ Before
$badgeClass = '';
if ($status === 'Done') {
    $badgeClass = 'bg-green-100 text-green-800';
} elseif ($status === 'Missed') {
    $badgeClass = 'bg-red-100 text-red-800';
}

// ✅ After
$badgeClass = ImmunizationStatus::from($status)->badgeClass();
```

### 4. Validation

```php
// ❌ Before
if (!in_array($status, ['Upcoming', 'Done', 'Missed'])) {
    throw new \Exception('Invalid status');
}

// ✅ After
try {
    $statusEnum = ImmunizationStatus::from($status);
} catch (\ValueError $e) {
    throw new \Exception('Invalid status');
}
```

## Migration Guide

### Updating Existing Code

**Before:**
```php
$immunization->status = 'Upcoming';
if ($immunization->status === 'Done') {
    // ...
}
```

**After:**
```php
use App\Enums\ImmunizationStatus;

$immunization->status = ImmunizationStatus::UPCOMING->value;
if ($immunization->status === ImmunizationStatus::DONE->value) {
    // ...
}
```

### Validation Rules

**Before:**
```php
'status' => 'required|in:Upcoming,Done,Missed'
```

**After:**
```php
use Illuminate\Validation\Rules\Enum;
use App\Enums\ImmunizationStatus;

'status' => ['required', new Enum(ImmunizationStatus::class)]
```

### Model Casting

```php
use App\Enums\ImmunizationStatus;

class Immunization extends Model
{
    protected $casts = [
        'status' => ImmunizationStatus::class,
    ];
}

// Now you can use:
$immunization->status = ImmunizationStatus::DONE;
// Saves as 'Done' in database

$status = $immunization->status; // Returns ImmunizationStatus enum
if ($status->isDone()) {
    // ...
}
```

## Blade Templates

**Before:**
```blade
@if($immunization->status === 'Done')
    <span class="bg-green-100 text-green-800">Done</span>
@endif
```

**After:**
```blade
@php
use App\Enums\ImmunizationStatus;
$statusEnum = ImmunizationStatus::from($immunization->status);
@endphp

<span class="{{ $statusEnum->badgeClass() }}">
    <i class="fas {{ $statusEnum->icon() }}"></i>
    {{ $statusEnum->label() }}
</span>
```

## JavaScript Integration

For frontend validation and dropdowns:

```javascript
// Generate from backend
const immunizationStatuses = @json(App\Enums\ImmunizationStatus::values());
// Result: ["Upcoming", "Done", "Missed"]

// Use in select dropdown
statuses.forEach(status => {
    const option = document.createElement('option');
    option.value = status;
    option.textContent = status;
    selectElement.appendChild(option);
});
```

## Best Practices

1. **Always use enum values**, not raw strings:
   ```php
   ✅ ImmunizationStatus::DONE->value
   ❌ 'Done'
   ```

2. **Use helper methods** for logic:
   ```php
   ✅ $status->isDone()
   ❌ $status === ImmunizationStatus::DONE
   ```

3. **Use enums in validation**:
   ```php
   ✅ new Enum(ImmunizationStatus::class)
   ❌ 'in:Upcoming,Done,Missed'
   ```

4. **Cast model attributes** to enums:
   ```php
   protected $casts = [
       'status' => ImmunizationStatus::class,
   ];
   ```

## Testing

```php
use App\Enums\ImmunizationStatus;

public function test_immunization_status_values()
{
    $values = ImmunizationStatus::values();
    $this->assertCount(3, $values);
    $this->assertContains('Upcoming', $values);
    $this->assertContains('Done', $values);
    $this->assertContains('Missed', $values);
}

public function test_status_helper_methods()
{
    $status = ImmunizationStatus::DONE;
    $this->assertTrue($status->isDone());
    $this->assertFalse($status->isMissed());
}
```

## Troubleshooting

### "Call to undefined method value()"

**Problem:** Trying to call `->value` on a string

**Solution:**
```php
// ❌ Wrong
$status = 'Done';
$value = $status->value; // Error!

// ✅ Correct
$status = ImmunizationStatus::DONE;
$value = $status->value; // Works!
```

### "ValueError: ... is not a valid backing value for enum"

**Problem:** Trying to convert invalid string to enum

**Solution:**
```php
// ❌ Wrong
$status = ImmunizationStatus::from('invalid'); // Throws ValueError

// ✅ Correct
try {
    $status = ImmunizationStatus::from($inputStatus);
} catch (\ValueError $e) {
    // Handle invalid status
}

// Or use tryFrom() which returns null on invalid value
$status = ImmunizationStatus::tryFrom($inputStatus);
if ($status === null) {
    // Handle invalid status
}
```

## Future Enhancements

Consider creating enums for:
- `PatientStatus` (Active, Inactive, Completed)
- `VaccineType` (BCG, Hepatitis B, DPT, OPV, MMR)
- `AppointmentStatus` (Scheduled, Completed, Cancelled, Rescheduled)
- `NotificationType` (SMS, Email, In-App)

## References

- [PHP 8.1 Enums Documentation](https://www.php.net/manual/en/language.enumerations.php)
- [Laravel Enum Casting](https://laravel.com/docs/10.x/eloquent-mutators#enum-casting)
- [Enum Validation Rule](https://laravel.com/docs/10.x/validation#rule-enum)
