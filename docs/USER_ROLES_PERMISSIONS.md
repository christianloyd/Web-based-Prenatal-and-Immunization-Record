 Healthcare Management System - User Roles & Permissions

 Overview
The Healthcare Management System implements a role-based access control (RBAC) system with two primary user roles: Midwife and Barangay Health Worker (BHW). Each role has specific permissions tailored to their responsibilities in the healthcare workflow.

 User Roles

 1. Midwife (Administrator Role)
Description: Licensed healthcare professionals with full system access and administrative privileges.

Primary Responsibilities:
- Complete patient care management
- Medical decision making
- System administration
- User management
- Data backup and security
- Comprehensive reporting

Access Level: Full administrative access to all system features

 2. Barangay Health Worker (BHW) (Limited User Role)
Description: Community health workers who provide basic healthcare services and data entry support.

Primary Responsibilities:
- Patient registration and basic information entry
- Data collection for prenatal checkups
- Child record maintenance
- Basic immunization tracking
- Community health monitoring

Access Level: Limited access to core functionality only

---

 Detailed Permission Matrix

 Authentication & Access Control

| Feature | Midwife | BHW | Notes |
|---------|---------|-----|-------|
| System Login | ✅ | ✅ | Both roles can log in |
| Dashboard Access | ✅ | ✅ | Role-specific dashboards |
| Profile Management | ✅ | ✅ | Own profile only |
| Session Management | ✅ | ✅ | Standard Laravel auth |

---

 Patient Management

| Feature | Midwife | BHW | Notes |
|---------|---------|-----|-------|
| View All Patients | ✅ | ✅ | Full patient list access |
| Create New Patient | ✅ | ✅ | Patient registration |
| View Patient Details | ✅ | ✅ | Complete patient information |
| Edit Patient Information | ✅ | ✅ | Update patient data |
| Delete Patient Records | ✅ | ✅ | Soft delete with recovery |
| Advanced Patient Search | ✅ | ✅ | Search by name, ID, etc. |

---

 Prenatal Care Management

| Feature | Midwife | BHW | Notes |
|---------|---------|-----|-------|
| View Prenatal Records | ✅ | ✅ | All prenatal care data |
| Create Prenatal Records | ✅ | ✅ | New pregnancy records |
| Edit Prenatal Records | ✅ | ✅ | Update pregnancy information |
| Delete Prenatal Records | ✅ | ✅ | Remove inactive records |
| Prenatal Checkups | ✅ | ✅ | Schedule and record checkups |
| Medical History Access | ✅ | ✅ | View patient medical history |
| Risk Assessment | ✅ | ✅ | High-risk pregnancy tracking |

---

 Appointment Management

| Feature | Midwife | BHW | Notes |
|---------|---------|-----|-------|
| View Appointments | ✅ | ✅ | All scheduled appointments |
| Create Appointments | ✅ | ✅ | Schedule new appointments |
| Edit Appointments | ✅ | ✅ | Modify appointment details |
| Cancel Appointments | ✅ | ✅ | Cancel with reason |
| Reschedule Appointments | ✅ | ✅ | Change date/time |
| Mark as Completed | ✅ | ✅ | Complete appointment |
| Appointment Reminders | ✅ | ✅ | Automated notification access |

---

 Child Health Management

| Feature | Midwife | BHW | Notes |
|---------|---------|-----|-------|
| View Child Records | ✅ | ✅ | All child health records |
| Create Child Records | ✅ | ✅ | Register new children |
| Edit Child Records | ✅ | ✅ | Update child information |
| Delete Child Records | ✅ | ✅ | Remove inactive records |
| Link to Mother | ✅ | ✅ | Associate with patient records |

---

 Immunization Management

| Feature | Midwife | BHW | Notes |
|---------|---------|-----|-------|
| View Immunizations | ✅ | ✅ | All immunization records |
| Schedule Immunizations | ✅ | ✅ | Plan vaccination schedules |
| Record Immunizations | ✅ | ✅ | Mark vaccines as given |
| Edit Immunization Records | ✅ | ✅ | Update vaccination data |
| Delete Immunization Records | ✅ | ✅ | Remove incorrect entries |
| Immunization History | ✅ | ✅ | Complete vaccination records |
| Due Date Tracking | ✅ | ✅ | Next vaccination alerts |

---

 Vaccine Inventory Management

| Feature | Midwife | BHW | Notes |
|---------|---------|-----|-------|
| View Vaccine Inventory | ✅ | ❌ | Stock levels and details |
| Add New Vaccines | ✅ | ❌ | Register new vaccine types |
| Edit Vaccine Information | ✅ | ❌ | Update vaccine details |
| Delete Vaccines | ✅ | ❌ | Remove discontinued vaccines |
| Stock Transactions | ✅ | ❌ | Record stock in/out |
| Low Stock Alerts | ✅ | ❌ | Inventory warnings |
| Expiry Date Tracking | ✅ | ❌ | Monitor vaccine expiration |

---

 User Management

| Feature | Midwife | BHW | Notes |
|---------|---------|-----|-------|
| View All Users | ✅ | ❌ | System user list |
| Create New Users | ✅ | ❌ | Add new BHW accounts |
| Edit User Information | ✅ | ❌ | Update user details |
| Delete Users | ✅ | ❌ | Remove user accounts |
| Activate/Deactivate Users | ✅ | ❌ | User status management |
| Role Assignment | ✅ | ❌ | Assign user roles |
| Permission Management | ✅ | ❌ | Configure access levels |

---

 System Backup & Recovery

| Feature | Midwife | BHW | Notes |
|---------|---------|-----|-------|
| View Backup History | ✅ | ❌ | List of all backups |
| Create Backups | ✅ | ❌ | Manual backup creation |
| Schedule Backups | ✅ | ❌ | Automated backup setup |
| Download Backups | ✅ | ❌ | Retrieve backup files |
| Restore from Backup | ✅ | ❌ | System recovery |
| Google Drive Integration | ✅ | ❌ | Cloud storage management |
| Backup Verification | ✅ | ❌ | Ensure backup integrity |

---

 Reporting & Analytics

| Feature | Midwife | BHW | Notes |
|---------|---------|-----|-------|
| Basic Reports | ✅ | ✅ | Standard health reports |
| Advanced Reports | ✅ | ❌ | Detailed analytics |
| Custom Report Generation | ✅ | ❌ | Configurable reports |
| Export to PDF | ✅ | ✅ | PDF report downloads |
| Export to Excel | ✅ | ✅ | Excel spreadsheet exports |
| Print Reports | ✅ | ✅ | Printer-friendly formats |
| Statistical Analysis | ✅ | ❌ | Advanced data analysis |

---

 Notifications & Alerts

| Feature | Midwife | BHW | Notes |
|---------|---------|-----|-------|
| System Notifications | ✅ | ✅ | In-app notifications |
| Appointment Reminders | ✅ | ✅ | Scheduled notifications |
| Immunization Alerts | ✅ | ✅ | Vaccination due notices |
| Stock Alerts | ✅ | ❌ | Low inventory warnings |
| System Maintenance | ✅ | ✅ | System status updates |
| Mark as Read | ✅ | ✅ | Notification management |
| Notification History | ✅ | ✅ | Past notifications |

---

 Role-Specific Features

 Midwife-Only Features
1. Complete System Administration
   - User account creation and management
   - System configuration and settings
   - Security and access control

2. Vaccine Inventory Management
   - Stock level monitoring
   - Supplier management
   - Expiry date tracking
   - Automated reorder alerts

3. Advanced Reporting
   - Custom report builder
   - Statistical analysis
   - Trend analysis
   - Performance metrics

4. Data Backup & Recovery
   - Automated backup scheduling
   - Google Drive integration
   - Disaster recovery procedures
   - Data integrity verification

5. Medical Decision Support
   - Risk assessment tools
   - Clinical guidelines
   - Medical history analysis
   - Treatment recommendations

 BHW-Only Limitations
1. No Administrative Access
   - Cannot create or manage user accounts
   - Cannot access system settings
   - Cannot perform backups

2. Limited Reporting
   - Basic reports only
   - Cannot create custom reports
   - No access to statistical analysis

3. No Inventory Management
   - Cannot manage vaccine stocks
   - Cannot track inventory
   - Cannot set reorder levels

4. Read-Only System Configuration
   - Cannot modify system settings
   - Cannot change notification preferences
   - Cannot configure integrations

---

 Security Implementation

 Authentication
- Laravel's built-in authentication system
- Secure password hashing (bcrypt)
- Session management with secure cookies
- CSRF protection on all forms
- Remember me functionality

 Authorization
- Role-based middleware for route protection
- Controller-level permission checks
- View-level content filtering
- Database-level access control

 Data Protection
- Soft deletes for data recovery
- Input validation and sanitization
- SQL injection protection via Eloquent ORM
- XSS prevention through Blade templating

---

 Access Control Flow

 Login Process
1. User enters credentials
2. System validates against database
3. User role is retrieved
4. Session is created with role information
5. User is redirected to role-specific dashboard

 Route Protection
1. Middleware checks authentication status
2. Role is verified against route requirements
3. Access is granted or denied
4. Unauthorized users redirected appropriately

 Feature Access
1. UI elements conditionally displayed based on role
2. Controller methods check permissions
3. Database queries filtered by access level
4. Error handling for unauthorized access

---

 Best Practices

 For System Administrators (Midwives)
- Regularly review user access levels
- Monitor system usage and audit trails
- Maintain current backups
- Keep user accounts updated
- Review and update permissions periodically

 For BHWs
- Focus on data entry accuracy
- Report system issues to midwives
- Follow established protocols
- Maintain patient confidentiality
- Use appropriate features for role

 Security Guidelines
- Use strong passwords
- Log out after use
- Don't share credentials
- Report suspicious activity
- Keep personal information updated

This role-based system ensures that each user has appropriate access to perform their duties while maintaining system security and data integrity.