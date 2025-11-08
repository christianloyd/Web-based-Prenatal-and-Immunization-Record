# API Endpoints Documentation

This document outlines all the API endpoints used in the Healthcare Management System and their purposes.

## API Types Used

### 1. REST API (Laravel Native)
- **Framework**: Laravel 11
- **Authentication**: Laravel Sanctum for API authentication, Session-based for web routes
- **Purpose**: Internal application API endpoints for healthcare management

### 2. External API Integration - Google Drive API
- **Service**: Google Drive API v3
- **Authentication**: OAuth2 with refresh token support
- **Purpose**: Cloud backup storage for healthcare data

## Internal REST API Endpoints

### Authentication & User Management
- **Base URL**: `/api/`
- **Authentication**: Laravel Sanctum middleware (`auth:sanctum`)

#### User Authentication
```
GET /api/user
Purpose: Get authenticated user information
Middleware: auth:sanctum
Response: User object with profile details
```

### Prenatal Management API
**Base URL**: `/api/prenatal/`
**Middleware**: `auth` (authenticated users only)

#### Core Prenatal Operations
```
GET /api/prenatal/
Purpose: List all prenatal records
Controller: PrenatalController@apiIndex

POST /api/prenatal/
Purpose: Create new prenatal record
Controller: PrenatalController@apiStore

GET /api/prenatal/{id}
Purpose: Get specific prenatal record
Controller: PrenatalController@apiShow

PUT /api/prenatal/{id}
Purpose: Update prenatal record
Controller: PrenatalController@apiUpdate

DELETE /api/prenatal/{id}
Purpose: Delete prenatal record
Controller: PrenatalController@apiDestroy
```

#### Dashboard & Statistics
```
GET /api/prenatal/stats
Purpose: Get dashboard statistics for prenatal data
Controller: PrenatalController@getDashboardStats
Response: Statistical data for dashboard widgets
```

#### Status Management
```
PATCH /api/prenatal/{id}/status
Purpose: Update prenatal record status
Controller: PrenatalController@updateStatus
```

## Web Routes (REST-like endpoints)

### Notification System API
**Base URL**: `/notifications/`

```
GET /notifications/unread-count
Purpose: Get count of unread notifications (cached)
Response: JSON with unread count

GET /notifications/recent
Purpose: Get recent notifications for dropdown (cached)
Response: JSON with recent notifications array

GET /notifications/new?last_check={timestamp}
Purpose: Get new notifications since timestamp (real-time polling)
Response: JSON with new notifications and unread count

POST /notifications/mark-as-read/{id}
Purpose: Mark specific notification as read
Response: JSON success/failure

POST /notifications/mark-all-as-read
Purpose: Mark all user notifications as read
Response: JSON success/failure

DELETE /notifications/{id}
Purpose: Delete specific notification
Response: JSON success/failure

POST /notifications/send-test
Purpose: Send test notification (development/demo)
Response: JSON success/failure
```

### Role-Based API Endpoints

#### Midwife Role Endpoints
**Base URL**: `/midwife/`

**Patient Management:**
- `GET /midwife/patients-search` - Search patients
- `GET /midwife/patients/{id}/profile` - Patient profile
- `GET /midwife/patients/{id}/print` - Print patient profile

**Prenatal Checkups:**
- `GET /midwife/prenatalcheckup/{id}/data` - Get checkup data
- `POST /midwife/prenatalcheckup/{id}/complete` - Mark checkup complete
- `PUT /midwife/prenatalcheckup/{id}/schedule` - Update schedule
- `POST /midwife/prenatalcheckup/{id}/mark-missed` - Mark as missed
- `POST /midwife/prenatalcheckup/{id}/reschedule` - Reschedule missed
- `GET /midwife/prenatalcheckup-patients/search` - Search patients with active prenatal records

**Appointments:**
- `POST /midwife/appointments/{id}/complete` - Mark appointment complete
- `POST /midwife/appointments/{id}/cancel` - Cancel appointment
- `POST /midwife/appointments/{id}/reschedule` - Reschedule appointment
- `GET /midwife/appointments-data/upcoming` - Get upcoming appointments
- `GET /midwife/appointments-data/today` - Get today's appointments

**Child Records & Immunizations:**
- `GET /midwife/childrecord-search` - Search child records
- `POST /midwife/childrecord/{childRecord}/immunizations` - Add immunization
- `PUT /midwife/childrecord/{childRecord}/immunizations/{immunization}` - Update immunization
- `DELETE /midwife/childrecord/{childRecord}/immunizations/{immunization}` - Delete immunization

**Immunization Management:**
- `GET /midwife/immunization/children-data` - Get children for immunization
- `GET /midwife/immunization/child/{childId}/vaccines` - Get available vaccines for child
- `GET /midwife/immunization/child/{childId}/vaccines/{vaccineId}/doses` - Get available doses
- `POST /midwife/immunization/{id}/quick-status` - Quick update immunization status

**Reports:**
- `POST /midwife/reports/generate` - Generate report
- `POST /midwife/reports/export-pdf` - Export report as PDF
- `POST /midwife/reports/export-excel` - Export report as Excel
- `GET /midwife/system-analysis-report` - Generate system analysis report

#### BHW (Barangay Health Worker) Role Endpoints
**Base URL**: `/bhw/`

Similar endpoints as Midwife but with restricted permissions:
- Patient management (view/edit)
- Prenatal records (view/edit)
- Child records and immunizations
- Basic reporting functions

#### Admin Role Endpoints
**Base URL**: `/admin/`

**View-Only Management:**
- `GET /admin/users` - List all users
- `GET /admin/users/{id}` - View user details
- `GET /admin/patients` - List all patients
- `GET /admin/patients/{id}` - View patient details
- `GET /admin/records` - View all records

**Cloud Backup Management:**
- `GET /admin/cloudbackup/data` - Get backup data
- `POST /admin/cloudbackup/create` - Create new backup
- `GET /admin/cloudbackup/progress/{id}` - Get backup progress
- `GET /admin/cloudbackup/download/{id}` - Download backup
- `POST /admin/cloudbackup/restore` - Restore from backup
- `DELETE /admin/cloudbackup/{id}` - Delete backup
- `POST /admin/cloudbackup/estimate-size` - Estimate backup size

## External API Integrations

### Google Drive API Integration
**Purpose**: Cloud backup storage for healthcare data
**Authentication**: OAuth2 with refresh tokens
**Service Class**: `App\Services\GoogleDriveService`

#### OAuth2 Flow Endpoints
```
GET /google/auth
Purpose: Redirect to Google OAuth2 authorization
Controller: GoogleAuthController@redirectToGoogle

GET /google/callback
Purpose: Handle OAuth2 callback from Google
Controller: GoogleAuthController@handleCallback

POST /google/disconnect
Purpose: Disconnect Google Drive integration
Controller: GoogleAuthController@disconnect
```

#### Google Drive Operations (Internal Service Methods)
- **Upload File**: Upload backup files to Google Drive
- **Download File**: Download backup files from Google Drive
- **Delete File**: Delete backup files from Google Drive
- **List Files**: List all backup files in Google Drive folder
- **Get File Info**: Get metadata for specific files
- **Storage Quota**: Check Google Drive storage usage
- **Test Connection**: Verify Google Drive connectivity

#### Google Drive API Features
- **Folder Management**: Automatically creates "Healthcare Backups" folder
- **File Upload**: Supports SQL backup file uploads
- **Token Management**: Automatic refresh token handling
- **Error Handling**: Comprehensive error logging and recovery
- **SSL Configuration**: Development-friendly SSL settings

## Security Features

### Authentication Methods
1. **Session-based Authentication**: For web interface
2. **Laravel Sanctum**: For API endpoints
3. **OAuth2**: For Google Drive integration
4. **Role-based Access Control**: Midwife, BHW, Admin roles

### API Security Measures
- **CSRF Protection**: Enabled for state-changing operations
- **Rate Limiting**: Implemented on API routes
- **Input Validation**: All inputs validated before processing
- **SQL Injection Protection**: Laravel Eloquent ORM used
- **XSS Protection**: Output sanitization implemented

## Performance Optimizations

### Caching Strategy
- **Notification Counts**: Cached for 30 seconds
- **Recent Notifications**: Cached for 60 seconds
- **User-specific Caching**: Separate cache keys per user

### Real-time Features
- **Notification Polling**: Real-time notification updates
- **Timestamp-based Updates**: Efficient polling mechanism
- **Cache Invalidation**: Automatic cache clearing on updates

## Data Formats

### Request/Response Format
- **Content-Type**: `application/json`
- **Response Format**: Standardized JSON responses
- **Error Format**: Consistent error response structure

### File Handling
- **Upload Format**: Multipart form data
- **Backup Files**: SQL format for database backups
- **Export Formats**: PDF and Excel for reports

## API Usage Examples

### Authentication Example
```php
// Get authenticated user
GET /api/user
Headers: Authorization: Bearer {token}
```

### Notification Example
```php
// Get unread count
GET /notifications/unread-count
Response: {"count": 5}

// Mark as read
POST /notifications/mark-as-read/123
Response: {"success": true}
```

### Google Drive Integration Example
```php
// Initiate OAuth
GET /google/auth
// Redirects to Google authorization page

// Handle callback
GET /google/callback?code={auth_code}
// Processes authorization and stores tokens
```

This API structure supports a comprehensive healthcare management system with role-based access, cloud backup capabilities, and real-time notifications for efficient patient care management.