 Laravel Healthcare Management System - Database Schema Documentation

 Overview
This healthcare management system is designed for prenatal care and child immunization tracking. The database supports user authentication, patient management, prenatal records, child health records, immunization scheduling, vaccine inventory, and appointment management.

 Core Tables Structure

 1. Users Table (`users`)
Purpose: Stores healthcare workers (midwives and BHWs) who use the system

Key Columns:
- `id` (Primary Key): Auto-incrementing user ID
- `name` (VARCHAR): Full name of the healthcare worker
- `gender` (ENUM): 'male', 'female', 'other'
- `age` (TINYINT): Age of the user
- `username` (VARCHAR, UNIQUE): Login username (email field removed)
- `password` (VARCHAR): Hashed password
- `contact_number` (VARCHAR, NULLABLE): Phone number
- `address` (VARCHAR, NULLABLE): Physical address
- `role` (ENUM): 'midwife', 'bhw' (default: 'midwife')
- `is_active` (BOOLEAN): User active status (default: true)
- `remember_token` (VARCHAR): For "Remember Me" functionality
- `timestamps`: created_at, updated_at

Relationships:
- One-to-many with `appointments` (conducted_by)
- One-to-many with `cloud_backups` (created_by)

Indexes:
- Primary key on `id`
- Unique index on `username`

---

 2. Patients Table (`patients`)
Purpose: Stores pregnant women who receive prenatal care

Key Columns:
- `id` (Primary Key): Auto-incrementing patient ID
- `formatted_patient_id` (VARCHAR, UNIQUE): Human-readable patient ID
- `name` (VARCHAR): Full name of the patient
- `age` (INTEGER): Age of the patient
- `contact` (VARCHAR, NULLABLE): Primary contact number
- `emergency_contact` (VARCHAR, NULLABLE): Emergency contact number
- `address` (TEXT, NULLABLE): Patient's address
- `occupation` (VARCHAR, NULLABLE): Patient's occupation
- `timestamps`: created_at, updated_at
- `deleted_at` (TIMESTAMP, NULLABLE): Soft delete timestamp

Relationships:
- One-to-many with `prenatal_records`
- One-to-many with `appointments`
- One-to-many with `child_records` (as mother)

Indexes:
- Primary key on `id`
- Unique index on `formatted_patient_id`
- Index on `name`

---

 3. Prenatal Records Table (`prenatal_records`)
Purpose: Tracks pregnancy information and medical history for each patient

Key Columns:
- `id` (Primary Key): Auto-incrementing record ID
- `formatted_prenatal_id` (VARCHAR, UNIQUE): Human-readable prenatal ID
- `patient_id` (Foreign Key): References `patients.id` (CASCADE DELETE)
- `last_menstrual_period` (DATE): LMP date for pregnancy calculation
- `expected_due_date` (DATE, NULLABLE): Calculated due date
- `gestational_age` (VARCHAR, NULLABLE): Current gestational age
- `trimester` (INTEGER, NULLABLE): Current trimester (1, 2, or 3)
- `gravida` (INTEGER, NULLABLE): Number of pregnancies
- `para` (INTEGER, NULLABLE): Number of births
- `medical_history` (TEXT, NULLABLE): Patient's medical history
- `notes` (TEXT, NULLABLE): Additional notes
- `last_visit` (DATE, NULLABLE): Date of last visit
- `next_appointment` (DATETIME, NULLABLE): Next scheduled appointment
- `status` (ENUM): 'normal', 'monitor', 'high-risk', 'due', 'completed' (default: 'normal')
- `blood_pressure` (VARCHAR, NULLABLE): Stored as "120/80" format
- `weight` (DECIMAL(5,2), NULLABLE): Weight in kg
- `height` (INTEGER, NULLABLE): Height in cm
- `is_active` (BOOLEAN): Active pregnancy status
- `timestamps`: created_at, updated_at
- `deleted_at` (TIMESTAMP, NULLABLE): Soft delete timestamp

Relationships:
- Many-to-one with `patients`
- One-to-many with `appointments`

Indexes:
- Primary key on `id`
- Unique index on `formatted_prenatal_id`
- Foreign key index on `patient_id`
- Index on `status`
- Index on `last_menstrual_period`
- Index on `expected_due_date`

---

 4. Child Records Table (`child_records`)
Purpose: Stores information about children born to patients for immunization tracking

Key Columns:
- `id` (Primary Key): Auto-incrementing child ID
- `formatted_child_id` (VARCHAR, UNIQUE): Human-readable child ID
- `child_name` (VARCHAR): Full name of the child
- `gender` (ENUM): 'Male', 'Female'
- `birth_height` (DECIMAL(6,2), NULLABLE): Birth height in cm
- `birth_weight` (DECIMAL(6,3), NULLABLE): Birth weight in kg
- `birthdate` (DATE): Date of birth
- `birthplace` (VARCHAR, NULLABLE): Place of birth
- `address` (TEXT, NULLABLE): Current address
- `father_name` (VARCHAR, NULLABLE): Father's full name
- `mother_name` (VARCHAR, NULLABLE): Mother's full name
- `phone_number` (VARCHAR(20)): Contact number
- `mother_id` (Foreign Key, NULLABLE): References `patients.id` (SET NULL)
- `timestamps`: created_at, updated_at

Relationships:
- Many-to-one with `patients` (as mother)
- One-to-many with `immunizations`
- One-to-many with `child_immunizations`

Indexes:
- Primary key on `id`
- Unique index on `formatted_child_id`
- Index on `child_name`
- Index on `birthdate`
- Foreign key index on `mother_id`

---

 5. Vaccines Table (`vaccines`)
Purpose: Master list of available vaccines with stock management

Key Columns:
- `id` (Primary Key): Auto-incrementing vaccine ID
- `formatted_vaccine_id` (VARCHAR, UNIQUE): Human-readable vaccine ID
- `name` (VARCHAR): Vaccine name
- `category` (VARCHAR): Vaccine category/type
- `dosage` (VARCHAR): Recommended dosage
- `dose_count` (INTEGER): Number of doses required (default: 1)
- `current_stock` (INTEGER): Current stock level (default: 0)
- `min_stock` (INTEGER): Minimum stock threshold (default: 10)
- `expiry_date` (DATE): Vaccine expiration date
- `storage_temp` (VARCHAR): Required storage temperature
- `notes` (TEXT, NULLABLE): Additional notes
- `timestamps`: created_at, updated_at

Relationships:
- One-to-many with `stock_transactions`
- One-to-many with `immunizations`

Indexes:
- Primary key on `id`
- Unique index on `formatted_vaccine_id`
- Index on `name`
- Index on `category`
- Index on `expiry_date`

---

 6. Stock Transactions Table (`stock_transactions`)
Purpose: Tracks all vaccine inventory movements (in/out)

Key Columns:
- `id` (Primary Key): Auto-incrementing transaction ID
- `vaccine_id` (Foreign Key): References `vaccines.id` (CASCADE DELETE)
- `transaction_type` (ENUM): 'in', 'out'
- `quantity` (INTEGER): Number of units moved
- `previous_stock` (INTEGER): Stock level before transaction
- `new_stock` (INTEGER): Stock level after transaction
- `reason` (VARCHAR): Reason for the transaction
- `timestamps`: created_at, updated_at

Relationships:
- Many-to-one with `vaccines`

Indexes:
- Primary key on `id`
- Foreign key index on `vaccine_id`
- Index on `transaction_type`
- Index on `created_at`

---

 7. Immunizations Table (`immunizations`)
Purpose: Schedules and tracks upcoming immunizations for children

Key Columns:
- `id` (Primary Key): Auto-incrementing immunization ID
- `formatted_immunization_id` (VARCHAR, UNIQUE): Human-readable immunization ID
- `child_record_id` (Foreign Key): References `child_records.id` (CASCADE DELETE)
- `vaccine_id` (Foreign Key, NULLABLE): References `vaccines.id` (RESTRICT DELETE)
- `vaccine_name` (VARCHAR): Name of the vaccine
- `dose` (VARCHAR): Dose information
- `schedule_date` (DATE): Scheduled immunization date
- `schedule_time` (TIME): Scheduled immunization time
- `status` (ENUM): 'Upcoming', 'Done', 'Missed' (default: 'Upcoming')
- `notes` (TEXT, NULLABLE): Additional notes
- `next_due_date` (DATE, NULLABLE): Next dose due date
- `timestamps`: created_at, updated_at

Relationships:
- Many-to-one with `child_records`
- Many-to-one with `vaccines`

Indexes:
- Primary key on `id`
- Unique index on `formatted_immunization_id`
- Foreign key index on `child_record_id`
- Foreign key index on `vaccine_id`
- Index on `status`
- Index on `schedule_date`
- Composite index on `child_record_id` and `status`

---

 8. Child Immunizations Table (`child_immunizations`)
Purpose: Records completed immunizations for children

Key Columns:
- `id` (Primary Key): Auto-incrementing record ID
- `child_record_id` (Foreign Key): References `child_records.id` (CASCADE DELETE)
- `vaccine_name` (VARCHAR): Name of administered vaccine
- `vaccine_description` (TEXT, NULLABLE): Description of the vaccine
- `vaccination_date` (DATE): Date vaccine was administered
- `administered_by` (VARCHAR): Name of person who administered vaccine
- `batch_number` (VARCHAR, NULLABLE): Vaccine batch number
- `notes` (TEXT, NULLABLE): Additional notes
- `next_due_date` (VARCHAR, NULLABLE): When next dose is due
- `timestamps`: created_at, updated_at

Relationships:
- Many-to-one with `child_records`

Indexes:
- Primary key on `id`
- Foreign key index on `child_record_id`

---

 9. Appointments Table (`appointments`)
Purpose: Manages all types of appointments including prenatal checkups

Key Columns:
- `id` (Primary Key): Auto-incrementing appointment ID
- `formatted_appointment_id` (VARCHAR, UNIQUE): Human-readable appointment ID
- `patient_id` (Foreign Key): References `patients.id` (CASCADE DELETE)
- `prenatal_record_id` (Foreign Key, NULLABLE): References `prenatal_records.id` (CASCADE DELETE)
- `appointment_date` (DATE): Scheduled appointment date
- `appointment_time` (TIME): Scheduled appointment time
- `type` (ENUM): 'prenatal_checkup', 'follow_up', 'consultation', 'emergency' (default: 'prenatal_checkup')
- `status` (ENUM): 'scheduled', 'completed', 'cancelled', 'rescheduled', 'no_show' (default: 'scheduled')
- `conducted_by` (Foreign Key, NULLABLE): References `users.id` (SET NULL)
- `notes` (TEXT, NULLABLE): Appointment notes
- `cancellation_reason` (TEXT, NULLABLE): Reason for cancellation
- `rescheduled_from_date` (DATE, NULLABLE): Original date if rescheduled
- `rescheduled_from_time` (TIME, NULLABLE): Original time if rescheduled
- `timestamps`: created_at, updated_at
- `deleted_at` (TIMESTAMP, NULLABLE): Soft delete timestamp

Relationships:
- Many-to-one with `patients`
- Many-to-one with `prenatal_records`
- Many-to-one with `users` (conducted_by)

Indexes:
- Primary key on `id`
- Unique index on `formatted_appointment_id`
- Composite index on `patient_id` and `appointment_date`
- Composite index on `status` and `appointment_date`
- Index on `type`

---

 10. Notifications Table (`notifications`)
Purpose: Laravel's built-in notification system for system alerts

Key Columns:
- `id` (UUID, Primary Key): Unique notification identifier
- `type` (VARCHAR): Notification class type
- `notifiable_type` (VARCHAR): Polymorphic type (usually User)
- `notifiable_id` (BIGINT): Polymorphic ID
- `data` (TEXT): JSON data containing notification content
- `read_at` (TIMESTAMP, NULLABLE): When notification was read
- `timestamps`: created_at, updated_at

Relationships:
- Polymorphic relationship with notifiable entities (primarily users)

Indexes:
- Primary key on `id`
- Composite index on `notifiable_type` and `notifiable_id`
- Additional indexes added for performance

---

 11. Cloud Backups Table (`cloud_backups`)
Purpose: Manages database backups stored in Google Drive

Key Columns:
- `id` (Primary Key): Auto-incrementing backup ID
- `name` (VARCHAR): Backup name
- `type` (ENUM): 'full', 'selective'
- `format` (VARCHAR): Backup format (default: 'sql_dump')
- `modules` (JSON): Array of selected modules to backup
- `file_path` (VARCHAR, NULLABLE): Local path to backup file
- `file_size` (VARCHAR, NULLABLE): Size of backup file
- `status` (ENUM): 'pending', 'in_progress', 'completed', 'failed' (default: 'pending')
- `storage_location` (VARCHAR): Storage location (default: 'google_drive')
- `encrypted` (BOOLEAN): Whether backup is encrypted (default: true)
- `compressed` (BOOLEAN): Whether backup is compressed (default: true)
- `verified` (BOOLEAN): Whether backup was verified (default: false)
- `google_drive_file_id` (VARCHAR, NULLABLE): Google Drive file ID
- `google_drive_link` (TEXT, NULLABLE): Google Drive web view link
- `error_message` (TEXT, NULLABLE): Error details if backup fails
- `started_at` (TIMESTAMP, NULLABLE): Backup start time
- `completed_at` (TIMESTAMP, NULLABLE): Backup completion time
- `created_by` (Foreign Key): References `users.id` (CASCADE DELETE)
- `timestamps`: created_at, updated_at

Relationships:
- Many-to-one with `users` (created_by)

Indexes:
- Primary key on `id`
- Foreign key index on `created_by`
- Composite index on `status` and `created_at`
- Composite index on `type` and `created_at`

---

 Supporting Tables

 Password Reset Tokens (`password_reset_tokens`)
- `email` (Primary Key): User email
- `token` (VARCHAR): Reset token
- `created_at` (TIMESTAMP): Token creation time

 Sessions (`sessions`)
- `id` (Primary Key): Session ID
- `user_id` (Foreign Key, NULLABLE): References users.id
- `ip_address` (VARCHAR): User IP address
- `user_agent` (TEXT): Browser user agent
- `payload` (LONGTEXT): Session data
- `last_activity` (INTEGER): Last activity timestamp

 Cache (`cache`) and Jobs (`jobs`)
- Standard Laravel caching and job queue tables

---

 Entity Relationship Diagram Summary

```
Users (1) ──── (∞) Appointments
Users (1) ──── (∞) Cloud Backups

Patients (1) ──── (∞) Prenatal Records
Patients (1) ──── (∞) Appointments
Patients (1) ──── (∞) Child Records

Prenatal Records (1) ──── (∞) Appointments

Child Records (1) ──── (∞) Immunizations
Child Records (1) ──── (∞) Child Immunizations

Vaccines (1) ──── (∞) Stock Transactions
Vaccines (1) ──── (∞) Immunizations

Notifications (polymorphic) ──── Users
```

 Data Flow

1. Patient Registration: Patients are registered in the `patients` table
2. Prenatal Care: Each patient gets a `prenatal_records` entry to track pregnancy
3. Appointments: Scheduled through the `appointments` table linking patients and prenatal records
4. Child Birth: When a child is born, they're added to `child_records` with mother reference
5. Immunization Scheduling: Future immunizations are scheduled in `immunizations` table
6. Vaccine Administration: Completed immunizations are recorded in `child_immunizations`
7. Stock Management: Vaccine inventory tracked through `vaccines` and `stock_transactions`
8. System Backups: Automated backups managed through `cloud_backups` table

 Key Features

- Soft Deletes: Implemented on patients, prenatal records, and appointments
- Formatted IDs: Human-readable IDs for most entities for better UX
- Polymorphic Notifications: Flexible notification system
- Stock Tracking: Complete vaccine inventory management
- Google Drive Integration: Cloud backup with Google Drive storage
- Role-based Access: Midwife and BHW roles with different permissions
- Comprehensive Indexing: Optimized for common queries and relationships

This schema supports a complete healthcare management workflow from patient registration through prenatal care, child birth, and ongoing immunization tracking, with robust backup and notification systems.