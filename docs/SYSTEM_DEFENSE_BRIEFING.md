# System Defense Briefing

## 1. Cloud Backup Logic (Google Drive Integration)

### Key Components
- `DatabaseBackupService` orchestrates SQL dump generation, determines selective/full scopes, and updates `CloudBackup` status metadata.@app/Services/DatabaseBackupService.php#40-243
- `GoogleDriveService` manages OAuth/service-account credentials, Drive folder lookup/creation, and upload/download/delete operations via the Google SDK.@app/Services/GoogleDriveService.php#17-155
- `CloudBackupController` (midwife namespace) surfaces backup dashboards, creation, Drive sync, download/restore, and deletion actions.@app/Http/Controllers/Midwife/CloudBackupController.php#24-524
- `AppServiceProvider` wires optional Google Drive bindings into `DatabaseBackupService` only when credentials exist.@app/Providers/AppServiceProvider.php#24-64
- `CloudBackup` model stores module selections, storage location, Drive file IDs, verification flags, and timestamps for auditing.@app/Models/CloudBackup.php#13-50

### Operational Flow
1. Midwife triggers `store`, creating a `CloudBackup` entry with `pending` status before delegating to `DatabaseBackupService` for execution.@app/Http/Controllers/Midwife/CloudBackupController.php#223-265
2. `createBackup()` switches status to `in_progress`, generates the SQL dump (entire DB or selected tables), attempts Drive upload when tokens exist, then marks `completed`/`failed` with file size and Drive metadata.@app/Services/DatabaseBackupService.php#43-99
3. Helper methods expose Drive connectivity, storage, and module configuration for dashboard displays (e.g., `getBackupStats`, `testGoogleDriveConnection`).
4. Sync operations read Google Drive folder contents, create any missing `CloudBackup` rows, and associate them with the initiating user.@app/Http/Controllers/Midwife/CloudBackupController.php#52-107

### Panel Emphasis Points
- Backups are SQL dumps with verification flags supporting selective or full restore paths.
- OAuth2 ensures the barangay-owned Google account controls the Drive folder; automatic token refresh keeps integration healthy, while failures gracefully fall back to local storage with detailed logs.
- Robust logging (info/error) provides an audit trail for backup attempts, Drive connectivity issues, and restore operations.

---

## 2. SMS Notification Workflow

### Core Services
- `SmsService` centralizes IPROG API calls, normalizes numbers, and records outcomes in `SmsLog` entries.@app/Services/SmsService.php#17-173
- `SendSmsJob` executes SMS delivery with retries/backoff so transient failures can recover without duplicate sends.@app/Jobs/SendSmsJob.php#13-148
- `ImmunizationService` triggers reminder/completion/missed/reschedule SMS (via `SendVaccinationReminderJob` or `SendSmsJob`) while managing vaccine and child state transitions.@app/Services/ImmunizationService.php#55-467
- Prenatal flows rely on `PrenatalCheckupController` and `PrenatalCheckupService` to notify mothers when visits are missed or rescheduled.@app/Http/Controllers/PrenatalCheckupController.php#612-685 @app/Services/PrenatalCheckupService.php#320-353

### Process Overview
1. Scheduling an immunization dispatches a reminder SMS with child, caregiver, vaccine, and schedule details.@app/Services/ImmunizationService.php#62-88
2. Status changes (Done/Missed) execute domain logic—stock deduction, child immunization record creation—and follow up with tailored SMS notifications.@app/Services/ImmunizationService.php#255-467
3. Marking a prenatal checkup as missed immediately alerts the patient/guardian and logs the attempt.@app/Http/Controllers/PrenatalCheckupController.php#612-684
4. `SmsService` captures every attempt, success, and failure, returning structured responses for monitoring dashboards and logs.

### Talking Points
- Some jobs run synchronously (`dispatchSync`) to guarantee delivery even without an active queue worker—useful during demos or on single-server deployments.
- Safeguards include phone normalization, structured logging, and retry logic; `SmsLog` views let staff audit communication history.
- Emphasize patient engagement benefits: reminders, completion notices, and missed alerts reduce no-shows and maintain continuity of care.

---

## 3. “Mark as Missed” Behavior (Immunization & Prenatal)

### Current Implementation
- **Immunization UI:** "Mark as Missed" buttons display for every `Upcoming` record that has not been rescheduled yet, without enforcing same-day checks.@resources/views/midwife/immunization/index.blade.php#184-205
- **Prenatal UI:** All upcoming checkups expose the action, regardless of current date.@resources/views/midwife/prenatalcheckup/index.blade.php#127-155
- **Backend:** Controllers ensure role authorization and required fields (reason, optional reschedule data) but do not confirm that today equals the scheduled date.@app/Http/Controllers/ImmunizationController.php#304-352 @app/Http/Controllers/PrenatalCheckupController.php#612-685
- **Services:** Status updates, reasons, SMS, and rescheduling logic execute consistently once a miss is recorded.@app/Services/ImmunizationService.php#336-467 @app/Services/PrenatalCheckupService.php#308-353

### Clarification for Defense
- Requirements specify the miss action should only appear on "D-day." Communicate that UI/backend guards will be tightened by validating `schedule_date == today` before allowing the transition.
- Example guard (conceptual):
  ```php
  if ($immunization->schedule_date->isFuture()) {
      return back()->with('error', 'You can only mark as missed on the scheduled day.');
  }
  ```
- Reinforce that SMS templates already reference the scheduled date, so enforcing the same-day rule protects data integrity and aligns notifications with reality.

---

## 4. Suggested Talking Points for Defense
1. **System Reliability**
   - Dual-path backups (local + Drive) with metadata verification and restore tooling ensure recoverability.
   - Describe fallback behavior when Drive is unreachable—system completes local backup and logs the issue.

2. **Data Security & Privacy**
   - OAuth tokens live under `storage/app/google`—no credentials are hard-coded.
   - SMS content sticks to reminders, avoiding sensitive medical data.

3. **Automation & Engagement**
   - Clarify why synchronous SMS dispatch protects clinics without queue workers.
   - Highlight automatic rescheduling flows that prevent data loss and keep guardians informed.

4. **Transparency About Limitations**
   - Acknowledge the current "Mark as Missed" limitation and outline the remediation plan (date validation + UI conditionals).

---

## 5. Example Panel Questions (with Response Hints)
1. **Drive Backup Verification:** Point to the `verified` flag, timestamps, and restore testing via `RestoreOperation` records.
2. **Preventing SMS Spam:** Mention job retry limits (3 attempts, 60-second backoff) and `SmsLog` auditing for duplicate detection.
3. **Handling Updated Phone Numbers:** Contact details are stored on the mother/patient record; changes propagate automatically because SMS services re-fetch each time.
4. **Avoiding Premature Miss Statuses:** Explain immediate plans for enforcing schedule-date validation and hiding the action until the correct day.
5. **Why Google Drive:** Accessible, low-cost, familiar to rural clinics, and the `GoogleDriveService` abstraction allows swapping providers later.

---

## 6. Additional Preparation Ideas
- Rehearse a short demo: schedule immunization → confirm SMS log → (post-fix) mark as missed on D-day → demonstrate reschedule flow.
- Export sample backup metadata from `cloud_backups` to showcase auditability.
- Prepare contingency statements if Google OAuth is disconnected (expected behavior plus manual workaround).
- Practice the "gap acknowledgement" narrative to show proactive improvement planning.

Good luck with your defense—this briefing consolidates the critical flow explanations, risk acknowledgements, and panel readiness material.
