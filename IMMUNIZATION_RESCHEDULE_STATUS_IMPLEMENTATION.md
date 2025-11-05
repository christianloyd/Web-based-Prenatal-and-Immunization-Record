# Immunization Reschedule Status Implementation

## Overview

Implemented the same "Rescheduled" status tracking mechanism in the Immunization system that already exists in the Prenatal Checkup system. This prevents duplicate rescheduling and maintains a clear audit trail of appointment changes.

---

## Problem Statement

**Before:** Immunization appointments could be rescheduled multiple times without tracking, causing:
- ❌ No record of which appointments were rescheduled
- ❌ Possible duplicate rescheduling of the same appointment
- ❌ No link between original and rescheduled appointments
- ❌ Difficult to track appointment history

**After:**
- ✅ Original appointments marked as "rescheduled" with a flag
- ✅ Link established between original and new appointments
- ✅ Prevent duplicate rescheduling
- ✅ Complete audit trail maintained

---

## Database Changes

### Migration: `2025_11_05_051514_add_rescheduled_fields_to_immunizations_table.php`

**New Columns Added:**

| Column | Type | Default | Purpose |
|--------|------|---------|---------|
| `rescheduled` | boolean | false | Flag indicating if this immunization was rescheduled |
| `rescheduled_to_immunization_id` | unsignedBigInteger | null | Foreign key linking to the new rescheduled immunization |

**Foreign Key Constraint:**
```php
$table->foreign('rescheduled_to_immunization_id')
      ->references('id')
      ->on('immunizations')
      ->onDelete('set null');
```

---

## Model Updates

### File: `app/Models/Immunization.php`

#### 1. **Fillable Fields Updated**
```php
protected $fillable = [
    // ... existing fields
    'rescheduled',                      // NEW
    'rescheduled_to_immunization_id'    // NEW
];
```

#### 2. **New Relationships Added**
```php
// Link to the new rescheduled immunization
public function rescheduledToImmunization()
{
    return $this->belongsTo(Immunization::class, 'rescheduled_to_immunization_id');
}

// Inverse: get the original immunization (if this one is a rescheduled version)
public function originalImmunization()
{
    return $this->hasOne(Immunization::class, 'rescheduled_to_immunization_id');
}
```

#### 3. **New Helper Methods**
```php
// Check if this immunization has been rescheduled
public function hasBeenRescheduled()
{
    return $this->rescheduled === true || $this->rescheduled === 1;
}

// Check if this immunization can be rescheduled
public function canBeRescheduled()
{
    // Can only reschedule if:
    // 1. Status is Upcoming (not Done or Missed)
    // 2. Has not already been rescheduled
    return $this->status === 'Upcoming' && !$this->hasBeenRescheduled();
}
```

---

## Service Layer Updates

### File: `app/Services/ImmunizationService.php`

#### Updated `markAsMissed()` Method

When an immunization is marked as missed with reschedule option:

**BEFORE:**
```php
// Create new immunization
$rescheduledImmunization = Immunization::create([...]);

// Original immunization just marked as "Missed"
// No connection between old and new records
```

**AFTER:**
```php
// Create new immunization
$rescheduledImmunization = Immunization::create([...]);

// Mark original as rescheduled and link to new immunization
$immunization->rescheduled = true;
$immunization->rescheduled_to_immunization_id = $rescheduledImmunization->id;

// Log the reschedule action
Log::info('Immunization rescheduled', [
    'original_id' => $immunization->id,
    'new_id' => $rescheduledImmunization->id,
    'child_id' => $immunization->child_record_id,
    'original_date' => $immunization->schedule_date,
    'new_date' => $newScheduleDate
]);
```

---

## Data Flow

### Rescheduling Process:

```
┌─────────────────────────────────────────────────────────────────┐
│ Original Immunization                                           │
│ - ID: 10                                                        │
│ - Status: Upcoming                                              │
│ - Schedule Date: 2025-11-10                                     │
│ - rescheduled: false                                            │
│ - rescheduled_to_immunization_id: null                          │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ User clicks "Reschedule"
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ Mark as Missed Modal                                            │
│ ☑ Yes, reschedule                                               │
│ New Date: 2025-11-15                                            │
│ New Time: 10:00 AM                                              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ Submit
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ Service Layer Processing                                        │
│ 1. Create new immunization (ID: 11)                            │
│ 2. Update original immunization:                               │
│    - Status: Missed                                             │
│    - rescheduled: true                                          │
│    - rescheduled_to_immunization_id: 11                         │
│ 3. Send SMS to parent                                           │
│ 4. Log reschedule action                                        │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌────────────────────────────────┬────────────────────────────────┐
│ Original Immunization (#10)   │ New Immunization (#11)         │
│ - Status: Missed               │ - Status: Upcoming             │
│ - rescheduled: TRUE            │ - Schedule Date: 2025-11-15    │
│ - rescheduled_to: 11          │ - Notes: "Rescheduled from..." │
└────────────────────────────────┴────────────────────────────────┘
```

---

## Frontend Integration

### Preventing Duplicate Reschedules

**In Blade Views (e.g., `midwife/immunization/index.blade.php`):**

```blade
@if($immunization->status === 'Upcoming' && !$immunization->hasBeenRescheduled())
    <!-- Show "Mark as Missed" button -->
    <button onclick="openMarkAsMissedModal({{ $immunization->id }})">
        Mark as Missed
    </button>
@endif

@if($immunization->hasBeenRescheduled())
    <!-- Show "Rescheduled" badge -->
    <span class="badge badge-info">
        <i class="fas fa-calendar-alt"></i> Rescheduled
    </span>
    <!-- Optionally show link to new appointment -->
    @if($immunization->rescheduledToImmunization)
        <a href="{{ route('midwife.immunization.show', $immunization->rescheduled_to_immunization_id) }}">
            View New Appointment
        </a>
    @endif
@endif
```

### Display Reschedule Status

```blade
<!-- In immunization details page -->
@if($immunization->rescheduled)
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        This appointment was rescheduled.
        @if($immunization->rescheduledToImmunization)
            New appointment: {{ $immunization->rescheduledToImmunization->schedule_date->format('M d, Y') }}
        @endif
    </div>
@endif

@if($immunization->originalImmunization)
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        This appointment was rescheduled from: {{ $immunization->originalImmunization->schedule_date->format('M d, Y') }}
    </div>
@endif
```

---

## Benefits

### 1. **Prevents Duplicate Rescheduling**
```php
// Before reschedule action:
if (!$immunization->canBeRescheduled()) {
    return response()->json([
        'success' => false,
        'message' => 'This immunization cannot be rescheduled. It may have already been rescheduled or completed.'
    ]);
}
```

### 2. **Complete Audit Trail**
```php
// Query all rescheduled immunizations
$rescheduled = Immunization::where('rescheduled', true)->get();

// Find original appointment
$original = $immunization->originalImmunization;

// Find where it was rescheduled to
$newAppointment = $immunization->rescheduledToImmunization;
```

### 3. **Better Reporting**
```sql
-- Count how many immunizations were rescheduled
SELECT COUNT(*) FROM immunizations WHERE rescheduled = 1;

-- Find reschedule patterns
SELECT
    i1.id as original_id,
    i1.schedule_date as original_date,
    i2.id as new_id,
    i2.schedule_date as new_date,
    DATEDIFF(i2.schedule_date, i1.schedule_date) as days_delayed
FROM immunizations i1
LEFT JOIN immunizations i2 ON i1.rescheduled_to_immunization_id = i2.id
WHERE i1.rescheduled = 1;
```

### 4. **SMS Tracking**
```php
// When sending SMS, know if it's for a rescheduled appointment
if ($immunization->originalImmunization) {
    $message = "Reminder: Your rescheduled vaccination appointment...";
} else {
    $message = "Reminder: Your vaccination appointment...";
}
```

---

## Pattern Consistency

This implementation follows the **exact same pattern** as the Prenatal Checkup reschedule system:

| Feature | Prenatal Checkup | Immunization |
|---------|-----------------|--------------|
| Reschedule Flag | `rescheduled` (boolean) | `rescheduled` (boolean) ✅ |
| Link to New Record | `rescheduled_to_checkup_id` | `rescheduled_to_immunization_id` ✅ |
| Foreign Key Constraint | Yes | Yes ✅ |
| Model Relationships | `rescheduledToCheckup()` | `rescheduledToImmunization()` ✅ |
| Helper Methods | `hasBeenRescheduled()`, `canBeRescheduled()` | Same ✅ |
| Service Integration | Updates flag when rescheduling | Updates flag when rescheduling ✅ |
| Logging | Logs reschedule action | Logs reschedule action ✅ |

---

## Testing Checklist

### Database:
- [ ] Run migration: `php artisan migrate`
- [ ] Verify columns exist: `DESCRIBE immunizations;`
- [ ] Check foreign key: `SHOW CREATE TABLE immunizations;`

### Model:
- [ ] Test `hasBeenRescheduled()` method
- [ ] Test `canBeRescheduled()` method
- [ ] Test `rescheduledToImmunization` relationship
- [ ] Test `originalImmunization` relationship

### Service Layer:
- [ ] Mark an immunization as missed without reschedule → Should NOT set rescheduled flag
- [ ] Mark an immunization as missed WITH reschedule → Should set rescheduled flag and link
- [ ] Try to reschedule an already rescheduled immunization → Should prevent

### Frontend:
- [ ] Upcoming immunization shows "Mark as Missed" button
- [ ] Rescheduled immunization shows "Rescheduled" badge
- [ ] Rescheduled immunization does NOT show "Mark as Missed" button
- [ ] Click "Mark as Missed" → Modal opens
- [ ] Check "Yes, reschedule" → Date fields appear
- [ ] Submit reschedule → Original marked as rescheduled, new appointment created
- [ ] Verify SMS sent to parent

---

## Example Queries

### Find all rescheduled immunizations for a child:
```php
$rescheduled = Immunization::where('child_record_id', $childId)
                           ->where('rescheduled', true)
                           ->with('rescheduledToImmunization')
                           ->get();
```

### Find immunizations that can be rescheduled:
```php
$canReschedule = Immunization::where('status', 'Upcoming')
                             ->where('rescheduled', false)
                             ->get();
```

### Find the reschedule chain:
```php
$chain = [];
$current = $immunization;

// Walk backwards to find original
while ($current->originalImmunization) {
    $current = $current->originalImmunization;
}
$chain[] = $current;

// Walk forwards to find all rescheduled versions
while ($current->rescheduledToImmunization) {
    $current = $current->rescheduledToImmunization;
    $chain[] = $current;
}

// $chain now contains: [original, rescheduled_1, rescheduled_2, ...]
```

---

## Migration Commands

```bash
# Create migration
php artisan make:migration add_rescheduled_fields_to_immunizations_table --table=immunizations

# Run migration
php artisan migrate

# Rollback if needed
php artisan migrate:rollback
```

---

**Date:** 2025-11-05
**Status:** ✅ Implemented - Ready for Testing
**Pattern:** Same as Prenatal Checkup Reschedule System
**Prevents:** Duplicate rescheduling, maintains audit trail
