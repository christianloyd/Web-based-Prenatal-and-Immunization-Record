# Duplicate SMS Issue - Analysis & Fix

## Issue Summary

**Problem:** When scheduling a prenatal checkup, the patient receives **TWO SMS messages**:
1. **General SMS** - "Next Appointment Scheduled: Hi ! Your next p..."
2. **Specific SMS** - "Hello Mara Dunlap! This is a reminder for your..."

**Date:** Nov 04, 12:34 AM (both sent at same time)
**Recipient:** Mara Dunlap (639497428155)

---

## Root Cause Analysis

### The SMS Flow:

```
Patient schedules prenatal checkup
         |
         v
PrenatalCheckupService::createCheckup()
         |
         ├─> Creates checkup record
         |
         ├─> scheduleNextVisit()
         |     |
         |     └─> Creates next checkup with status 'upcoming'
         |           |
         |           └─> sendCheckupReminder() ✉️ SMS #1 (Specific type)
         |
         └─> PrenatalCheckupObserver fires (Model event)
               |
               └─> created() event
                     |
                     └─> NotificationService::sendAppointmentConfirmation() ✉️ SMS #2 (General type)
```

---

## The Duplicate SMS Problem

### SMS #1: From PrenatalCheckupService (Line 151)
**File:** `app/Services/PrenatalCheckupService.php`
**Location:** Line 150-151 in `scheduleNextVisit()` method

```php
protected function scheduleNextVisit(array $data, $patient, $prenatalRecord)
{
    if (!$existingNextCheckup) {
        $nextCheckup = PrenatalCheckup::create([...]);

        // Send SMS reminder for next visit
        $this->sendCheckupReminder($patient, $nextCheckup); // ✉️ SMS #1
    }
}
```

**SMS Details:**
- **Type:** `prenatal_checkup_reminder`
- **Message:** "Hello {Name}! This is a reminder for your prenatal checkup scheduled on {date} at {time}. Please arrive on time. Thank you!"
- **Database Type:** "Prenatal Checkup Reminder"

---

### SMS #2: From PrenatalCheckupObserver (Line 19)
**File:** `app/Observers/PrenatalCheckupObserver.php`
**Location:** Line 13-21 in `created()` event

```php
public function created(PrenatalCheckup $prenatalCheckup): void
{
    // Send SMS for NEXT VISIT DATE only
    if (!empty($prenatalCheckup->next_visit_date)) {
        NotificationService::sendAppointmentConfirmation($prenatalCheckup); // ✉️ SMS #2
    }
}
```

**Which calls:**
**File:** `app/Services/NotificationService.php`
**Location:** Line 29-52

```php
public static function sendAppointmentConfirmation(PrenatalCheckup $checkup)
{
    $patient->notify(new HealthcareNotification(
        'Next Appointment Scheduled',
        "Hi {$patientName}! Your next prenatal checkup has been scheduled for {date}...",
        'info',
        null,
        [...],
        true // Enable SMS ✉️
    ));
}
```

**SMS Details:**
- **Type:** `general` (from HealthcareNotification)
- **Message:** "Next Appointment Scheduled: Hi ! Your next prenatal checkup has been scheduled for {date}. You will receive a reminder 1 day before."
- **Database Type:** "General"

---

## Why Two Different Types?

### Type 1: "Prenatal Checkup Reminder"
- Sent via `SmsService::sendSms()` with explicit type parameter
- More specific message format
- Direct SMS sending

### Type 2: "General"
- Sent via `HealthcareNotification` (Laravel notification system)
- Goes through `SmsChannel`
- Categorized as "General" notification

---

## The Logic Conflict

### **What's happening:**

1. When scheduling a **next visit**, `scheduleNextVisit()` creates a new checkup with `status = 'upcoming'`

2. This triggers `PrenatalCheckup::create()` which fires the `created` event

3. The Observer sees the new checkup was created

4. **Problem:** Both the Service AND the Observer are sending SMS!

### **Timeline:**
```
12:34 AM - scheduleNextVisit() calls sendCheckupReminder()
              ↓
           SMS #1 sent: "Hello Mara Dunlap! This is a reminder..."

12:34 AM - PrenatalCheckup::create() fires 'created' event
              ↓
           Observer::created() triggered
              ↓
           NotificationService::sendAppointmentConfirmation()
              ↓
           SMS #2 sent: "Next Appointment Scheduled: Hi ! Your next p..."
```

---

## Solutions

### Option 1: Remove SMS from scheduleNextVisit() ✅ RECOMMENDED

Remove the direct SMS call from the Service and let ONLY the Observer handle it.

**File:** `app/Services/PrenatalCheckupService.php`

```php
protected function scheduleNextVisit(array $data, $patient, $prenatalRecord)
{
    if (!$existingNextCheckup) {
        $nextCheckup = PrenatalCheckup::create([
            'patient_id' => $patient->id,
            'prenatal_record_id' => $prenatalRecord ? $prenatalRecord->id : null,
            'checkup_date' => $data['next_visit_date'],
            'checkup_time' => $data['next_visit_time'] ?? '09:00',
            'status' => 'upcoming',
            'conducted_by' => Auth::id(),
        ]);

        // REMOVE THIS LINE:
        // $this->sendCheckupReminder($patient, $nextCheckup);

        // Observer will handle SMS sending automatically
    }
}
```

**Pros:**
- Single source of truth (Observer)
- All checkup creations will send SMS consistently
- Cleaner separation of concerns

**Cons:**
- None

---

### Option 2: Remove Observer SMS, Keep Service SMS ❌ NOT RECOMMENDED

Disable the Observer's SMS sending and keep only the Service's SMS.

**File:** `app/Observers/PrenatalCheckupObserver.php`

```php
public function created(PrenatalCheckup $prenatalCheckup): void
{
    // COMMENT OUT OR REMOVE:
    // if (!empty($prenatalCheckup->next_visit_date)) {
    //     NotificationService::sendAppointmentConfirmation($prenatalCheckup);
    // }

    // Only send in-app notifications, not SMS
}
```

**Pros:**
- More direct control in Service layer

**Cons:**
- Have to remember to call sendCheckupReminder() everywhere
- Inconsistent - some places might forget to send SMS
- Observer becomes less useful

---

### Option 3: Add Guard to Prevent Duplicate ⚠️ COMPLEX

Add a flag to prevent the Observer from sending SMS if Service already sent it.

**Not recommended** - adds unnecessary complexity

---

## Recommended Fix

### Step 1: Update PrenatalCheckupService.php

**Remove the manual SMS call and let the Observer handle it:**

```php
protected function scheduleNextVisit(array $data, $patient, $prenatalRecord)
{
    // Check if a checkup already exists for the next visit date
    $existingNextCheckup = PrenatalCheckup::where('patient_id', $patient->id)
        ->whereDate('checkup_date', $data['next_visit_date'])
        ->first();

    if (!$existingNextCheckup) {
        $nextCheckup = PrenatalCheckup::create([
            'patient_id' => $patient->id,
            'prenatal_record_id' => $prenatalRecord ? $prenatalRecord->id : null,
            'checkup_date' => $data['next_visit_date'],
            'checkup_time' => $data['next_visit_time'] ?? '09:00',
            'gestational_age_weeks' => null,
            'status' => 'upcoming',
            'conducted_by' => Auth::id(),
        ]);

        // Remove this line - Observer will handle it:
        // $this->sendCheckupReminder($patient, $nextCheckup);
    }
}
```

### Step 2: Update Observer to Use Better Message

**File:** `app/Observers/PrenatalCheckupObserver.php`

The Observer already handles it correctly! But we should make sure it's using the right message.

```php
public function created(PrenatalCheckup $prenatalCheckup): void
{
    // Send SMS for NEXT VISIT DATE only
    if (!empty($prenatalCheckup->next_visit_date)) {
        NotificationService::sendAppointmentConfirmation($prenatalCheckup);
    }
}
```

---

## Testing Plan

### Before Fix:
- [ ] Schedule a prenatal checkup with next visit
- [ ] Observe: 2 SMS messages sent
- [ ] Check SMS logs: Both "General" and "Prenatal Checkup Reminder"

### After Fix:
- [ ] Apply fix (remove SMS from scheduleNextVisit)
- [ ] Schedule a prenatal checkup with next visit
- [ ] Observe: Only 1 SMS message sent
- [ ] Check SMS logs: Only "General" type
- [ ] Verify message content is correct

---

## Additional Improvements

### Improve the SMS Message Quality

The "General" SMS shows:
```
"Next Appointment Scheduled: Hi ! Your next p..."
```

**Issues:**
1. Missing patient name after "Hi !"
2. Message cut off "Your next p..."

**Fix in NotificationService.php:**

```php
public static function sendAppointmentConfirmation(PrenatalCheckup $checkup)
{
    $patient = $checkup->prenatalRecord->patient ?? null;
    $patientName = $patient ? $patient->name : 'Patient'; // Use $patient->name

    $appointmentDate = $checkup->next_visit_date ?? $checkup->checkup_date;

    $message = "Hi {$patientName}! Your next prenatal checkup has been scheduled for " .
        \Carbon\Carbon::parse($appointmentDate)->format('F d, Y') .
        ". You will receive a reminder 1 day before. - HealthCare System";

    // Ensure message is not too long (160 char SMS limit for single message)
    if (strlen($message) > 160) {
        $message = "Hi {$patientName}! Your prenatal checkup is on " .
            \Carbon\Carbon::parse($appointmentDate)->format('M d, Y') .
            ". Reminder coming 1 day before.";
    }

    $patient->notify(new HealthcareNotification(
        'Next Appointment Scheduled',
        $message,
        'info',
        null,
        [
            'checkup_id' => $checkup->id,
            'next_visit_date' => $appointmentDate,
            'type' => 'confirmation'
        ],
        true // Enable SMS
    ));
}
```

---

## Summary

**Root Cause:** Two separate code paths sending SMS:
1. `PrenatalCheckupService::sendCheckupReminder()` - Direct SMS
2. `PrenatalCheckupObserver::created()` → `NotificationService` - Via Laravel notifications

**Solution:** Remove the direct SMS call from the Service, let the Observer handle all SMS sending via the notification system.

**Benefits:**
- ✅ Single SMS per appointment
- ✅ Consistent behavior
- ✅ Cleaner code
- ✅ Easier to maintain

---

**Date:** 2025-11-04
**Status:** Analysis Complete - Ready to Fix
**Estimated Fix Time:** 10 minutes
