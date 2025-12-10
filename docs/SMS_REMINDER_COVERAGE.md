# SMS Reminder Coverage

## Overview
This document consolidates all known SMS reminder and notification flows in the application. References cite the exact code locations responsible for dispatching each SMS.

## Prenatal Checkups
- **Next visit scheduling** – Creating or updating a checkup with a future visit invokes `scheduleNextVisit`, which creates an upcoming appointment and sends a "prenatal_checkup_reminder" SMS. @app/Services/PrenatalCheckupService.php#133-183
- **Completion confirmation** – Marking a checkup as completed triggers `sendCompletionSms`, notifying the patient and including the next visit if available. Message type: "prenatal_checkup_completed". @app/Services/PrenatalCheckupService.php#248-299
- **Manual missed marking** – Controller logic sets status to missed and dispatches a "prenatal_checkup_missed" SMS (reason-dependent messaging). @app/Http/Controllers/PrenatalCheckupController.php#612-684
- **Manual reschedule of missed checkup** – Rescheduling creates a new upcoming visit and reuses `sendCheckupReminder`, resulting in a "prenatal_checkup_rescheduled" SMS. @app/Http/Controllers/PrenatalCheckupController.php#695-820 @app/Services/PrenatalCheckupService.php#328-353
- **Prenatal notification service hooks** – `NotificationService` provides two additional touch points:
  1. Appointment confirmation on booking ("prenatal_checkup_confirmation"). @app/Services/NotificationService.php#39-83
  2. Day-before reminder ("prenatal_checkup_reminder"). @app/Services/NotificationService.php#95-132

## Child Immunizations
- **Schedule creation** – Initial immunization scheduling dispatches `SendVaccinationReminderJob`, sending an immediate appointment reminder SMS. @app/Services/ImmunizationService.php#27-90
- **Status updates via quick update** – `quickUpdateStatus` sends context-specific SMS messages:
  1. Status "Done" ⇒ "immunization_completed" confirmation. @app/Services/ImmunizationService.php#315-352
  2. Status "Missed" without reschedule ⇒ "missed_appointment" notification. @app/Services/ImmunizationService.php#449-456
  3. Missed with reschedule ⇒ creates a new record and sends "immunization_rescheduled" SMS. @app/Services/ImmunizationService.php#409-430
- **Child profile entry** – Adding a completed immunization through `ChildImmunizationController@store` dispatches `SendSmsJob` with "immunization_completed" messaging, including optional next-dose notes. @app/Http/Controllers/ChildImmunizationController.php#103-140

## System-Wide Vaccination Reminders
- `NotificationService::sendVaccinationReminder` targets children approaching due dates, covering both in-app alerts and guardian SMS reminders. @app/Services/NotificationService.php#137-187

## Infrastructure & Delivery
- **Job pipeline** – `SendSmsJob` encapsulates asynchronous dispatch, using `SmsService` for API delivery, logging, and retry-safe error handling. @app/Jobs/SendSmsJob.php#1-148 @app/Services/SmsService.php#1-173
- **Vaccination-specific queued job** – `SendVaccinationReminderJob` prepares reminder payloads for immunization schedules. @app/Jobs/SendVaccinationReminderJob.php#1-160

## Coverage Snapshot
| Domain | Scenario | Message Type | Dispatch Location |
| --- | --- | --- | --- |
| Prenatal | Upcoming/next visit scheduled | prenatal_checkup_reminder | `scheduleNextVisit` @app/Services/PrenatalCheckupService.php#133-183 |
| Prenatal | Appointment confirmation | prenatal_checkup_confirmation | `NotificationService::sendAppointmentConfirmation` @app/Services/NotificationService.php#39-83 |
| Prenatal | Day-before reminder | prenatal_checkup_reminder | `NotificationService::sendAppointmentReminder` @app/Services/NotificationService.php#95-132 |
| Prenatal | Completed visit | prenatal_checkup_completed | `sendCompletionSms` @app/Services/PrenatalCheckupService.php#248-299 |
| Prenatal | Missed visit | prenatal_checkup_missed | Controller handling @app/Http/Controllers/PrenatalCheckupController.php#612-684 |
| Prenatal | Rescheduled missed visit | prenatal_checkup_rescheduled | Controller + `sendCheckupReminder` @app/Http/Controllers/PrenatalCheckupController.php#695-820 |
| Immunization | Initial schedule | vaccination_reminder | `SendVaccinationReminderJob` @app/Services/ImmunizationService.php#27-90 |
| Immunization | Completed dose | immunization_completed | `quickUpdateStatus` / `ChildImmunizationController` @app/Services/ImmunizationService.php#315-352; @app/Http/Controllers/ChildImmunizationController.php#103-140 |
| Immunization | Missed dose | missed_appointment | `quickUpdateStatus` @app/Services/ImmunizationService.php#449-456 |
| Immunization | Rescheduled dose | immunization_rescheduled | `quickUpdateStatus` @app/Services/ImmunizationService.php#409-430 |

## Observed Gaps & Considerations
1. **Automated missed checkups** – Console commands (`AutoMarkMissedCheckups`, `MarkTodaysMissedCheckups`) currently mark appointments as missed without sending SMS. Evaluate if guardians should be contacted automatically. @app/Console/Commands/AutoMarkMissedCheckups.php#1-140 @app/Console/Commands/MarkTodaysMissedCheckups.php#28-74
2. **SMS failure handling** – Failures are logged but there is no automated retry or user-facing alert. Consider monitoring/log review protocols.
3. **Contact availability** – All flows gracefully skip SMS when contact numbers are absent. Ensure UI communicates when SMS will not be sent.
