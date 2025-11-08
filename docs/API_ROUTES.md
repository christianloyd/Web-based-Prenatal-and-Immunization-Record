# Healthcare Management System - API Routes Documentation

## Overview
This document outlines all the routes available in the Healthcare Management System. The system uses Laravel's web routes for handling all requests with role-based access control.

## Authentication Routes

### Public Routes
- `GET /` - Redirects to login page
- `GET /login` - Display login form
- `POST /login` - Process login authentication

### Google OAuth Routes
- `GET /google/auth` - Redirect to Google OAuth
- `GET /google/callback` - Handle Google OAuth callback
- `POST /google/disconnect` - Disconnect Google account

### Logout
- `POST /logout` - Logout user

## Dashboard Routes

### Main Dashboard
- `GET /dashboard` - Role-based dashboard redirect
  - Redirects to appropriate dashboard based on user role:
    - Midwife → `/midwife/dashboard`
    - BHW → `/bhw/dashboard`

## Notification Routes
**Prefix:** `/notifications`
**Name:** `notifications.*`

- `GET /notifications` - List all notifications
- `GET /notifications/unread-count` - Get unread notification count
- `GET /notifications/recent` - Get recent notifications
- `POST /notifications/mark-as-read/{id}` - Mark notification as read
- `POST /notifications/mark-all-as-read` - Mark all notifications as read
- `DELETE /notifications/{id}` - Delete notification
- `POST /notifications/send-test` - Send test notification

---

## Midwife Routes
**Prefix:** `/midwife`
**Name:** `midwife.*`
**Access:** Midwife role required

### Dashboard
- `GET /midwife/dashboard` - Midwife dashboard with statistics

### Patient Management
**Resource:** `midwife.patients.*`

- `GET /midwife/patients` - List all patients
- `GET /midwife/patients/create` - Show patient creation form
- `POST /midwife/patients` - Store new patient
- `GET /midwife/patients/{id}` - Show patient details
- `GET /midwife/patients/{id}/edit` - Show patient edit form
- `PUT /midwife/patients/{id}` - Update patient
- `DELETE /midwife/patients/{id}` - Delete patient

### Prenatal Records Management
**Resource:** `midwife.prenatalrecord.*`

- `GET /midwife/prenatalrecord` - List all prenatal records
- `GET /midwife/prenatalrecord/create` - Show prenatal record creation form
- `POST /midwife/prenatalrecord` - Store new prenatal record
- `GET /midwife/prenatalrecord/{id}` - Show prenatal record details
- `GET /midwife/prenatalrecord/{id}/edit` - Show prenatal record edit form
- `PUT /midwife/prenatalrecord/{id}` - Update prenatal record
- `DELETE /midwife/prenatalrecord/{id}` - Delete prenatal record

### Prenatal Checkups Management
**Resource:** `midwife.prenatalcheckup.*`

- `GET /midwife/prenatalcheckup` - List all prenatal checkups
- `GET /midwife/prenatalcheckup/create` - Show checkup creation form
- `POST /midwife/prenatalcheckup` - Store new checkup
- `GET /midwife/prenatalcheckup/{id}` - Show checkup details
- `GET /midwife/prenatalcheckup/{id}/edit` - Show checkup edit form
- `PUT /midwife/prenatalcheckup/{id}` - Update checkup
- `DELETE /midwife/prenatalcheckup/{id}` - Delete checkup

#### Additional Prenatal Checkup Routes
- `GET /midwife/prenatalcheckup/{id}/data` - Get checkup data (AJAX)
- `POST /midwife/prenatalcheckup/{id}/complete` - Mark checkup as completed

### Appointment Management
**Resource:** `midwife.appointments.*`

- `GET /midwife/appointments` - List all appointments
- `GET /midwife/appointments/create` - Show appointment creation form
- `POST /midwife/appointments` - Store new appointment
- `GET /midwife/appointments/{id}` - Show appointment details
- `GET /midwife/appointments/{id}/edit` - Show appointment edit form
- `PUT /midwife/appointments/{id}` - Update appointment
- `DELETE /midwife/appointments/{id}` - Delete appointment

#### Additional Appointment Routes
- `POST /midwife/appointments/{id}/complete` - Mark appointment as completed
- `POST /midwife/appointments/{id}/cancel` - Cancel appointment
- `POST /midwife/appointments/{id}/reschedule` - Reschedule appointment
- `GET /midwife/appointments-data/upcoming` - Get upcoming appointments (AJAX)
- `GET /midwife/appointments-data/today` - Get today's appointments (AJAX)

### Child Records Management
**Resource:** `midwife.childrecord.*`

- `GET /midwife/childrecord` - List all child records
- `GET /midwife/childrecord/create` - Show child record creation form
- `POST /midwife/childrecord` - Store new child record
- `GET /midwife/childrecord/{id}` - Show child record details
- `GET /midwife/childrecord/{id}/edit` - Show child record edit form
- `PUT /midwife/childrecord/{id}` - Update child record
- `DELETE /midwife/childrecord/{id}` - Delete child record

### Child Immunization Management
**Prefix:** `/midwife/childrecord/{childRecord}/immunizations`
**Name:** `midwife.childrecord.immunizations.*`

- `POST /midwife/childrecord/{childRecord}/immunizations` - Add immunization to child
- `PUT /midwife/childrecord/{childRecord}/immunizations/{immunization}` - Update child immunization
- `DELETE /midwife/childrecord/{childRecord}/immunizations/{immunization}` - Delete child immunization

### Immunization Management
**Resource:** `midwife.immunization.*`

- `GET /midwife/immunization` - List all immunizations
- `GET /midwife/immunization/create` - Show immunization creation form
- `POST /midwife/immunization` - Store new immunization
- `GET /midwife/immunization/{id}` - Show immunization details
- `GET /midwife/immunization/{id}/edit` - Show immunization edit form
- `PUT /midwife/immunization/{id}` - Update immunization
- `DELETE /midwife/immunization/{id}` - Delete immunization

#### Additional Immunization Routes
- `GET /midwife/immunization/child/{childId}/vaccines` - Get available vaccines for child (AJAX)
- `GET /midwife/immunization/child/{childId}/vaccines/{vaccineId}/doses` - Get available doses for child (AJAX)

### Vaccine Management
**Resource:** `midwife.vaccines.*`

- `GET /midwife/vaccines` - List all vaccines
- `GET /midwife/vaccines/create` - Show vaccine creation form
- `POST /midwife/vaccines` - Store new vaccine
- `GET /midwife/vaccines/{id}` - Show vaccine details
- `GET /midwife/vaccines/{id}/edit` - Show vaccine edit form
- `PUT /midwife/vaccines/{id}` - Update vaccine
- `DELETE /midwife/vaccines/{id}` - Delete vaccine

#### Additional Vaccine Routes
- `POST /midwife/vaccines/stock-transaction` - Record stock transaction

### Cloud Backup Management
**Prefix:** `/midwife/cloudbackup`
**Name:** `midwife.cloudbackup.*`

- `GET /midwife/cloudbackup` - List all backups
- `GET /midwife/cloudbackup/data` - Get backup data (AJAX)
- `POST /midwife/cloudbackup/create` - Create new backup
- `GET /midwife/cloudbackup/progress/{id}` - Get backup progress (AJAX)
- `GET /midwife/cloudbackup/download/{id}` - Download backup file
- `POST /midwife/cloudbackup/restore` - Restore from backup
- `DELETE /midwife/cloudbackup/{id}` - Delete backup
- `POST /midwife/cloudbackup/estimate-size` - Estimate backup size (AJAX)

### User Management
**Resource:** `midwife.user.*`

- `GET /midwife/user` - List all users
- `GET /midwife/user/create` - Show user creation form
- `POST /midwife/user` - Store new user
- `GET /midwife/user/{id}` - Show user details
- `GET /midwife/user/{id}/edit` - Show user edit form
- `PUT /midwife/user/{id}` - Update user
- `DELETE /midwife/user/{id}` - Delete user

#### Additional User Routes
- `PATCH /midwife/user/{user}/deactivate` - Deactivate user
- `PATCH /midwife/user/{user}/activate` - Activate user

### Reports
**Prefix:** `/midwife`
**Name:** `midwife.report.*`

- `GET /midwife/reports` - Show reports page
- `GET /midwife/reports/print` - Show print view for reports
- `POST /midwife/reports/generate` - Generate report
- `POST /midwife/reports/export-pdf` - Export report as PDF
- `POST /midwife/reports/export-excel` - Export report as Excel

---

## BHW (Barangay Health Worker) Routes
**Prefix:** `/bhw`
**Name:** `bhw.*`
**Access:** BHW role required

### Dashboard
- `GET /bhw/dashboard` - BHW dashboard with statistics

### Patient Management
**Resource:** `bhw.patients.*`

- `GET /bhw/patients` - List all patients
- `GET /bhw/patients/create` - Show patient creation form
- `POST /bhw/patients` - Store new patient
- `GET /bhw/patients/{id}` - Show patient details
- `GET /bhw/patients/{id}/edit` - Show patient edit form
- `PUT /bhw/patients/{id}` - Update patient
- `DELETE /bhw/patients/{id}` - Delete patient

### Prenatal Records Management
**Resource:** `bhw.prenatalrecord.*`

- `GET /bhw/prenatalrecord` - List all prenatal records
- `GET /bhw/prenatalrecord/create` - Show prenatal record creation form
- `POST /bhw/prenatalrecord` - Store new prenatal record
- `GET /bhw/prenatalrecord/{id}` - Show prenatal record details
- `GET /bhw/prenatalrecord/{id}/edit` - Show prenatal record edit form
- `PUT /bhw/prenatalrecord/{id}` - Update prenatal record
- `DELETE /bhw/prenatalrecord/{id}` - Delete prenatal record

### Prenatal Checkups Management
**Resource:** `bhw.prenatalcheckup.*`

- `GET /bhw/prenatalcheckup` - List all prenatal checkups
- `GET /bhw/prenatalcheckup/create` - Show checkup creation form
- `POST /bhw/prenatalcheckup` - Store new checkup
- `GET /bhw/prenatalcheckup/{id}` - Show checkup details
- `GET /bhw/prenatalcheckup/{id}/edit` - Show checkup edit form
- `PUT /bhw/prenatalcheckup/{id}` - Update checkup
- `DELETE /bhw/prenatalcheckup/{id}` - Delete checkup

#### Additional Prenatal Checkup Routes
- `GET /bhw/prenatalcheckup/{id}/data` - Get checkup data (AJAX)
- `POST /bhw/prenatalcheckup/{id}/complete` - Mark checkup as completed

### Child Records Management
**Resource:** `bhw.childrecord.*`

- `GET /bhw/childrecord` - List all child records
- `GET /bhw/childrecord/create` - Show child record creation form
- `POST /bhw/childrecord` - Store new child record
- `GET /bhw/childrecord/{id}` - Show child record details
- `GET /bhw/childrecord/{id}/edit` - Show child record edit form
- `PUT /bhw/childrecord/{id}` - Update child record
- `DELETE /bhw/childrecord/{id}` - Delete child record

### Child Immunization Management
**Prefix:** `/bhw/childrecord/{childRecord}/immunizations`
**Name:** `bhw.childrecord.immunizations.*`

- `POST /bhw/childrecord/{childRecord}/immunizations` - Add immunization to child
- `PUT /bhw/childrecord/{childRecord}/immunizations/{immunization}` - Update child immunization
- `DELETE /bhw/childrecord/{childRecord}/immunizations/{immunization}` - Delete child immunization

### Immunization Management
**Resource:** `bhw.immunizations.*`

- `GET /bhw/immunizations` - List all immunizations
- `GET /bhw/immunizations/create` - Show immunization creation form
- `POST /bhw/immunizations` - Store new immunization
- `GET /bhw/immunizations/{id}` - Show immunization details
- `GET /bhw/immunizations/{id}/edit` - Show immunization edit form
- `PUT /bhw/immunizations/{id}` - Update immunization
- `DELETE /bhw/immunizations/{id}` - Delete immunization

#### Additional Immunization Routes
- `GET /bhw/immunizations/child/{childId}/vaccines` - Get available vaccines for child (AJAX)
- `GET /bhw/immunizations/child/{childId}/vaccines/{vaccineId}/doses` - Get available doses for child (AJAX)

### Appointment Management (BHW)
**Resource:** `bhw.appointments.*`

- `GET /bhw/appointments` - List all appointments
- `GET /bhw/appointments/create` - Show appointment creation form
- `POST /bhw/appointments` - Store new appointment
- `GET /bhw/appointments/{id}` - Show appointment details
- `GET /bhw/appointments/{id}/edit` - Show appointment edit form
- `PUT /bhw/appointments/{id}` - Update appointment
- `DELETE /bhw/appointments/{id}` - Delete appointment

#### Additional Appointment Routes (BHW)
- `POST /bhw/appointments/{id}/complete` - Mark appointment as completed
- `POST /bhw/appointments/{id}/cancel` - Cancel appointment
- `POST /bhw/appointments/{id}/reschedule` - Reschedule appointment
- `GET /bhw/appointments-data/upcoming` - Get upcoming appointments (AJAX)
- `GET /bhw/appointments-data/today` - Get today's appointments (AJAX)

### Reports (BHW)
**Prefix:** `/bhw`
**Name:** `bhw.report.*`

- `GET /bhw/report` - Show reports page
- `GET /bhw/report/print` - Show print view for reports
- `POST /bhw/report/generate` - Generate report
- `POST /bhw/report/export-pdf` - Export report as PDF
- `POST /bhw/report/export-excel` - Export report as Excel

---

## Route Middleware

### Authentication Middleware
- All routes except public routes require authentication
- Login redirects unauthenticated users to login page

### Role-Based Access Control
- **Midwife Routes**: Full access to all functionality
- **BHW Routes**: Limited access, cannot manage:
  - Users
  - Vaccines (inventory management)
  - Cloud backups
  - Advanced reporting features

### CSRF Protection
- All POST, PUT, PATCH, DELETE requests require CSRF token
- Forms must include `@csrf` directive

## HTTP Methods Used

- **GET**: Retrieve/display data
- **POST**: Create new resources
- **PUT**: Update existing resources
- **PATCH**: Partial updates (activate/deactivate users)
- **DELETE**: Remove resources

## Response Formats

### Web Routes
- **HTML**: All routes return Blade templates
- **JSON**: AJAX endpoints return JSON responses
- **PDF**: Report exports return PDF files
- **Excel**: Report exports return Excel files
- **File Downloads**: Backup downloads return file responses

### Status Codes
- **200**: Successful requests
- **302**: Redirects after form submissions
- **403**: Access denied (role restrictions)
- **404**: Resource not found
- **422**: Validation errors
- **500**: Server errors

## Common URL Parameters

- `{id}`: Resource ID (integer)
- `{user}`: User model binding
- `{childRecord}`: Child record model binding
- `{immunization}`: Immunization model binding
- `{vaccineId}`: Vaccine ID for AJAX requests
- `{childId}`: Child ID for AJAX requests

## Security Features

- **Authentication**: Laravel's built-in authentication
- **Authorization**: Role-based access control
- **CSRF Protection**: All forms protected
- **Input Validation**: Server-side validation on all inputs
- **SQL Injection Protection**: Eloquent ORM prevents SQL injection
- **XSS Protection**: Blade templating engine escapes output by default

This routing structure provides a comprehensive healthcare management system with proper separation of concerns between different user roles and secure access controls.