# Healthcare Management System – Comprehensive Overview

## 1. Purpose & Scope
This document provides a consolidated view of the Healthcare Management System implemented in this repository. It is intended to help stakeholders prepare for presentations, onboard new contributors, and understand how the different healthcare workflows (prenatal care, child health, immunization, reporting, and backups) operate together.

---

## 2. Technology Stack & Deployment
- **Framework:** Laravel 10 with Blade templating and Tailwind CSS for the UI layer.@README.md#29-36
- **Database:** MySQL with migration-based schema management and seeding support.
- **Queue/Jobs:** Laravel queues (sync driver by default) backing background jobs such as SMS dispatch.@app/Jobs/SendSmsJob.php#13-148
- **External Services:** Google OAuth for authentication, Google Drive API for backups, and IPROG SMS API for messaging.@app/Services/GoogleDriveService.php#17-155 @app/Services/SmsService.php#17-173
- **Runtime:** Designed for XAMPP-based local deployment, with artisan/vite workflows for development.@README.md#140-155

---

## 3. Roles & Access Control
Two primary roles are supported—Midwife and Barangay Health Worker (BHW). Route groupings enforce role-specific dashboards and module permissions, with shared authentication and notification routes protected by middleware and rate limiting.@routes/web.php#40-244 

Key characteristics:
- Authenticated users are redirected to dashboards based on their role.
- Dedicated prefixes (`/midwife`, `/bhw`) compartmentalize features.
- Certain capabilities (e.g., cloud backups, user administration, inventory management) are restricted to midwives.

---

## 4. Application Architecture
### 4.1 Request Handling & Service Layer
Controllers remain thin and delegate complex logic to dedicated services (e.g., `ImmunizationService`, `PrenatalCheckupService`, `DatabaseBackupService`). Service bindings are registered in `AppServiceProvider`, which also wires optional dependencies like Google Drive integration and model observers for automated notifications.@app/Providers/AppServiceProvider.php#24-83

### 4.2 Domain Services & Jobs
- **ImmunizationService** encapsulates scheduling, completion, missed-status handling, SMS notifications, and rescheduling logic.@app/Services/ImmunizationService.php#20-487
- **PrenatalCheckupService** manages missed/rescheduled prenatal visits, often invoked by controllers during status changes.@app/Services/PrenatalCheckupService.php#302-357
- **DatabaseBackupService** generates SQL dumps, pushes them to Google Drive when available, and tracks metadata within the `cloud_backups` table.@app/Services/DatabaseBackupService.php#40-243
- **SendSmsJob** wraps SMS delivery with retry/backoff, orchestrating calls into `SmsService` while recording failures for later inspection.@app/Jobs/SendSmsJob.php#13-148

### 4.3 Notifications & Observers
Model observers are registered during application boot to automate in-app notifications for patient, vaccine, and prenatal events. Custom Blade directives support role-aware UI rendering.@app/Providers/AppServiceProvider.php#70-83

---

## 5. Core Domain Modules
### 5.1 Authentication & User Management
- Login via username/password with session regeneration and throttling.
- Optional Google OAuth linkage for Drive-backed features.@routes/web.php#27-38
- User CRUD, activation/deactivation, and role assignment live under the midwife namespace to keep administrative features segregated.@routes/web.php#152-161

### 5.2 Patient Registry
- Supports detailed demographic capture, formatted IDs, emergency contacts, and search endpoints to accelerate prenatal workflows.@routes/web.php#82-114
- Patient profiles are re-used throughout prenatal, appointment, and immunization modules for continuity of care.

### 5.3 Prenatal Record Management
- Tracks pregnancies, computed gestational metrics, and medical notes via `PrenatalRecordController` routes exposed to both roles.@routes/web.php#90-100 @routes/web.php#197-207
- Prenatal records can be marked complete at delivery and linked to newborn child records, establishing continuity between maternal and child modules.

### 5.4 Prenatal Checkups
- Upcoming checkups are scheduled, edited, or marked missed through controller actions that enforce role permissions and status validation.@app/Http/Controllers/PrenatalCheckupController.php#612-685
- Missed checkups trigger SMS alerts to patients and can be rescheduled with automated reminder dispatches via `PrenatalCheckupService`.

### 5.5 Appointment Scheduling
- Shared midwife/BHW appointment dashboard supports filtering, status updates, and rescheduling.
- Completing a prenatal appointment automatically spawns a `PrenatalCheckup` record, linking calendar operations with clinical documentation.@app/Http/Controllers/AppointmentController.php#120-208

### 5.6 Child Records & Immunizations
- Child registration captures birth and guardian data, linking to maternal patient profiles for SMS outreach.
- Immunization scheduling leverages vaccine dose intervals, updates inventory usage, and handles missed/rescheduled scenarios with corresponding SMS.
- UI actions for marking appointments done/missed are exposed for upcoming immunizations, with rescheduling flows gated on previous reschedule state.@resources/views/midwife/immunization/index.blade.php#184-210 @app/Services/ImmunizationService.php#258-467

### 5.7 Vaccine Inventory Management
- Midwife-only endpoints manage vaccine catalog entries, stock transactions, and inventory audits.@routes/web.php#121-136
- Stock deductions occur automatically when immunizations are marked complete, preventing allocation beyond available quantities.@app/Services/ImmunizationService.php#258-333

### 5.8 Notification System (In-app & SMS)
- `NotificationController` provides polling endpoints for unread counts and recent items, while individual services push notifications through `HealthcareNotification` channels.
- `NotificationService::sendVaccinationReminder` exemplifies combined in-app and SMS messaging when a child approaches due immunizations.@app/Services/NotificationService.php#140-187
- SMS flows centralize through `SmsService`, which normalizes phone numbers, logs results, and routes category-specific messages (vaccination reminders, missed visits, prenatal alerts).@app/Services/SmsService.php#17-173 @app/Services/SmsService.php#257-317

### 5.9 Cloud Backup & Restore
- Midwives can trigger selective or full SQL dumps, monitor progress, and restore from history using the Cloud Backup UI.
- Google Drive integration is optional; when credentials or tokens are absent, backups remain local with extensive logging for traceability.@app/Services/DatabaseBackupService.php#40-243 @app/Services/GoogleDriveService.php#17-155 @app/Http/Controllers/Midwife/CloudBackupController.php#24-352

### 5.10 Reporting & Analytics
- Generates PDF/Excel exports for prenatal statistics, immunization coverage, and BHW accomplishment reports using dedicated endpoints under each role.
- Reports support date filtering and share the same permission boundaries defined in the route groups.@routes/web.php#138-175 @routes/web.php#235-240

### 5.11 SMS Logs & Audit Trails
- Midwives and BHWs can review SMS history via `SmsLogController` routes to validate communication attempts.@routes/web.php#148-150 @routes/web.php#243-244

---

## 6. Cross-Cutting Concerns
- **Validation:** Combination of controller-level validation and FormRequest classes ensure data integrity before hitting services.
- **Transactions:** Critical flows (immunizations, prenatal status changes, backups) wrap writes in database transactions to avoid partial updates.@app/Services/ImmunizationService.php#27-104 @app/Services/ImmunizationService.php#258-476
- **Caching:** Notification counts are cached per user and purged when events fire, keeping UI badges responsive.@app/Http/Controllers/AppointmentController.php#229-246
- **Logging:** Extensive `Log::info` and `Log::error` calls capture success/failure paths across services, important for audits and failure analysis.

---

## 7. Data Model Highlights
- **Patients ↔ Prenatal Records:** One-to-many relationship enabling multiple pregnancies per patient.
- **Prenatal Records ↔ Checkups / Appointments:** Checkups inherit scheduling context from appointments; status transitions create downstream data automatically.@app/Http/Controllers/AppointmentController.php#176-208
- **Child Records ↔ Immunizations:** Each immunization links to a child record and associated vaccine, with status flags (`Upcoming`, `Done`, `Missed`) controlling UI actions and message templates.@app/Services/ImmunizationService.php#258-467
- **Cloud Backups:** `cloud_backups` table stores metadata (type, modules, storage location, Drive IDs) enabling restore operations and audit tracking.@app/Services/DatabaseBackupService.php#40-243

---

## 8. External Integrations
| Integration | Purpose | Key Implementation |
|-------------|---------|--------------------|
| Google OAuth | Authenticate midwives via Google before accessing Drive | `GoogleAuthController`, static OAuth helpers inside `GoogleDriveService` handle redirect & token storage.@app/Services/GoogleDriveService.php#40-75|
| Google Drive | Store verified SQL backups; manage download/delete | Drive client initialization, folder creation, and file uploads in `GoogleDriveService` with metadata returned to `DatabaseBackupService`.@app/Services/GoogleDriveService.php#80-155 @app/Services/DatabaseBackupService.php#52-88|
| IPROG SMS API | Send vaccination reminders, missed-appointment alerts, and confirmations | `SmsService` posts JSON payloads, logs attempts, and returns structured results for controllers/jobs.@app/Services/SmsService.php#35-173|

---

## 9. Operational Checklist
1. **Daily**
   - Review dashboards for today’s appointments and notifications.
   - Confirm immunization statuses and send follow-up SMS if needed.
2. **Weekly (Midwife)**
   - Run cloud backups (full or selective) and confirm Drive synchronization.
   - Audit vaccine stock levels and update inventory transactions.
   - Export reports for supervisory review.
3. **Monthly**
   - Validate Google OAuth tokens (renew if expired).
   - Review SMS logs for failed deliveries and contact families manually when required.
   - Inspect notification cache behavior and clear stale entries if necessary.

---

## 10. Known Limitations & Follow-Up Items
- **Mark-as-Missed Guardrail:** The immunization UI exposes the “Mark as Missed” action for any upcoming schedule, without verifying that the current date matches the scheduled date. This diverges from the intended rule (“only show on the exact D-day”). Applying a form/request check plus conditional rendering in Blade will align behavior with requirements.@resources/views/midwife/immunization/index.blade.php#184-210 @app/Services/ImmunizationService.php#336-467
- **SMS Queue Usage:** Several SMS flows dispatch synchronously (`dispatchSync`) to guarantee delivery when no queue worker is running. In production, shifting these to asynchronous jobs with monitoring would improve responsiveness.@app/Services/ImmunizationService.php#62-90 @app/Services/ImmunizationService.php#393-467
- **Password Policy & Endpoint Throttling:** Password validation remains lenient and only login routes have strict throttling. Harden password rules and extend rate limiting to sensitive endpoints for a stronger security posture.
- **Test Coverage:** PHPUnit scaffolding exists, but automated tests are limited. Prioritize service/unit tests for immunization, prenatal, and backup workflows before release.

---

## 11. Document Maintenance
- File: `docs/HEALTHCARE_SYSTEM_OVERVIEW.md`
- Owner: Development/Documentation team
- Update cadence: Review quarterly or after major feature releases to ensure accuracy.

This overview complements existing deep-dive documents in the `/docs` directory (e.g., workflow analyses, improvement plans) and should be referenced alongside module-specific guides when preparing for demonstrations or audits.
