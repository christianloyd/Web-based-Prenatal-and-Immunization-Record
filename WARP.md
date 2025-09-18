 WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

 Project Overview

This is a Laravel 11 healthcare management system designed for prenatal care, child health records, and immunization tracking. The system serves two primary user roles: Midwives (full access) and Barangay Health Workers (BHW, limited access).

 Essential Development Commands

 Initial Setup
```powershell
 Install PHP dependencies
composer install

 Install Node.js dependencies  
npm install

 Environment setup
cp .env.example .env
php artisan key:generate

 Database operations
php artisan migrate
php artisan db:seed
```

 Development Workflow
```powershell
 Start development server
php artisan serve

 Build frontend assets (development)
npm run dev

 Build frontend assets (production)
npm run build

 Run tests
php artisan test
 Or specific test suites
.\vendor\bin\phpunit tests/Unit
.\vendor\bin\phpunit tests/Feature

 Code quality checks
.\vendor\bin\pint   Laravel Pint for code formatting
```

 Database Management
```powershell
 Fresh migration with seeding
php artisan migrate:fresh --seed

 Create new migration
php artisan make:migration create_table_name

 Create new seeder
php artisan make:seeder TableNameSeeder

 Run specific seeder
php artisan db:seed --class=TableNameSeeder
```

 Cache Management
```powershell
 Clear all caches (common troubleshooting step)
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

 Optimize for production
php artisan optimize
```

 Architecture Overview

 Role-Based Architecture
The application implements a dual-role system with role-specific routing:
- Midwife routes: Prefixed with `/midwife/` - Full system access including user management, cloud backups, and vaccine inventory
- BHW routes: Prefixed with `/bhw/` - Limited access focused on patient care and basic reporting

 Key Domain Models and Relationships
- User: Handles authentication with role-based access (Midwife/BHW)
- Patient: Base patient information with relationships to prenatal and child records
- PrenatalRecord: Manages pregnancy tracking with associated checkups
- PrenatalCheckup: Individual prenatal visit records
- ChildRecord: Child health information with immunization tracking
- ChildImmunization: Vaccine administration records linked to specific vaccines and doses
- Vaccine: Inventory management with stock tracking
- Immunization: Legacy immunization system (being phased out in favor of ChildImmunization)

 Service Layer Architecture
- GoogleDriveService: Handles OAuth2 authentication and cloud backup operations
- DatabaseBackupService: Manages local and cloud database backup creation
- NotificationService: System-wide notification handling

 Authentication Flow
1. Standard Laravel authentication with username/password
2. Optional Google OAuth integration for secure access
3. Session-based authentication with role-based route protection
4. Google Drive integration requires OAuth2 setup for cloud backup functionality

 Frontend Architecture
- Blade templates with Tailwind CSS for styling
- DaisyUI components for enhanced UI elements
- Vite for asset bundling and hot reloading
- Role-specific view directories: `resources/views/midwife/` and `resources/views/bhw/`

 Database Schema Patterns

 Key Relationships
- Patients have one-to-many relationships with both PrenatalRecord and ChildRecord
- PrenatalRecord has one-to-many PrenatalCheckup entries
- ChildRecord has one-to-many ChildImmunization entries
- Vaccines maintain stock levels through StockTransaction entries
- All major entities use formatted IDs (e.g., "P-001", "PR-001", "CR-001") for user-friendly display

 Migration Patterns
- Database uses conventional Laravel timestamp columns
- Most tables include soft deletes for data integrity
- Stock transactions maintain audit trails for vaccine inventory
- Notifications table uses polymorphic relationships for flexible notification sources

 Development Patterns

 Controller Organization
Controllers are organized by domain with role-specific access:
- Patient management (shared between roles)
- Prenatal care workflows (PrenatalRecordController, PrenatalCheckupController)
- Child health tracking (ChildRecordController, ChildImmunizationController)
- Inventory management (VaccineController - Midwife only)
- System administration (UserController, CloudBackupController - Midwife only)

 Model Conventions
- All models include comprehensive validation rules as static methods
- Accessor methods for formatted display values (formatted_contact_number, status_badge_class)
- Scope methods for common queries (active(), byRole(), etc.)
- Models use traits for shared functionality (NotifiesHealthcareWorkers)

 Observer Pattern Usage
Key models use observers for automated actions:
- PatientObserver: Handles patient lifecycle events
- PrenatalCheckupObserver: Manages checkup-related notifications
- VaccineObserver: Tracks inventory changes and low-stock alerts

 Testing Strategy

 Test Structure
- Unit tests: Focus on model validation, relationships, and business logic
- Feature tests: Test complete workflows including authentication, role-based access, and CRUD operations
- Tests use SQLite in-memory database for fast execution
- Factory classes provide consistent test data generation

 Running Tests
```powershell
 Full test suite
php artisan test

 Specific test methods
php artisan test --filter=test_method_name

 Generate coverage report (if configured)
php artisan test --coverage
```

 Google Drive Integration

 OAuth2 Setup Requirements
1. Google Cloud Console project with Drive API enabled
2. OAuth2 credentials stored in `storage/app/google/oauth_credentials.json`
3. Access tokens managed in `storage/app/google/token.json`
4. Automatic token refresh handling in GoogleDriveService

 Cloud Backup Workflow
- Database backups are created as SQL dumps
- Files uploaded to dedicated "Healthcare Backups" folder
- Backup metadata stored in CloudBackup model
- Progress tracking for large backup operations

 Key Configuration Files

 Environment Variables
Critical `.env` settings:
- Database connection (MySQL required)
- Google OAuth credentials for Drive integration
- Application URL for proper asset loading
- Queue configuration for background jobs

 Asset Pipeline
- Vite configuration: Handles CSS/JS bundling with Tailwind processing
- Tailwind config: Custom color scheme and component paths
- PostCSS: Autoprefixer integration for browser compatibility

 Troubleshooting Common Issues

 Permission Issues (Windows/XAMPP)
- Ensure `storage/` and `bootstrap/cache/` directories are writable
- Use `php artisan storage:link` for public file access

 Google Drive Authentication
- Check OAuth2 credentials path and format
- Verify token expiration and refresh token availability
- Test connection with `php artisan tinker` and GoogleDriveService

 Asset Loading Problems
- Run `npm run dev` for development or `npm run build` for production
- Clear browser cache after asset changes
- Verify Vite configuration matches deployment environment

 Database Connection Issues
- Confirm MySQL service is running (XAMPP)
- Verify database exists and credentials in `.env`
- Check PHP extensions: pdo_mysql, mysqli

 Backup Restore Issues
- "There is no active transaction" error: Fixed in DatabaseBackupService by ensuring transactions are properly managed during restore operations
- Foreign key constraint violations: Restore process now handles table dependencies in correct order (parent tables before child tables)
- Truncated SQL statements: Improved SQL statement parsing to handle multi-line INSERT statements properly
- Test restore functionality using: `php artisan tinker` and calling `app(App\Services\DatabaseBackupService::class)->restoreBackup($backup)`
