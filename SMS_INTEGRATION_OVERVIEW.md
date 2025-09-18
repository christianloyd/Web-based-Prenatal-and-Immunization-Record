 SMS Integration Overview - Semaphore API

 Purpose
Integrate SMS reminders specifically for:
1. Patients with upcoming prenatal checkups
2. Parents with children due for immunizations

 Prerequisites
- [ ] Semaphore account (sign up at semaphore.co)
- [ ] API key from Semaphore dashboard
- [ ] SMS credits/load purchased

 Current System Analysis
✅ Existing notification system (`NotificationService.php`)
✅ Phone number storage ready:
- `patients.contact` - for prenatal appointment reminders
- `child_records.phone_number` - for vaccination reminders
✅ Scheduled notification triggers already implemented
✅ Laravel notification system in place

 Technical Requirements

 1. Laravel Package
```bash
composer require humans/semaphore-sms
```

 2. Environment Configuration
Add to `.env`:
```env
SEMAPHORE_API_KEY=your_api_key_here
SEMAPHORE_SENDER_NAME=your_sender_name
```

 3. Database Requirements
Existing fields are sufficient:
- `patients.contact`
- `child_records.phone_number`

Optional: Create `sms_logs` table for tracking:
- `id`, `phone_number`, `message`, `status`, `sent_at`, `response_data`

 Integration Points

 Prenatal Checkup Reminders
- Target: `patients.contact` field
- Trigger: Existing `NotificationService::checkUpcomingAppointments()`
- Sample Message:
  ```
  Hi [Patient Name], you have a prenatal checkup tomorrow at [Time].
  Please confirm your attendance.
  ```

 Child Immunization Reminders
- Target: `child_records.phone_number` field (parent's number)
- Trigger: Existing `NotificationService::checkVaccinationsDue()`
- Sample Message:
  ```
  Hi, your child [Child Name] is due for [Vaccine Name] immunization.
  Please visit the health center.
  ```

 Implementation Steps

 Step 1: Setup
1. Install Semaphore SMS package
2. Configure environment variables
3. Create SMS notification channel

 Step 2: Extend Existing Services
1. Modify `NotificationService::sendAppointmentReminder()` to include SMS
2. Modify `NotificationService::sendVaccinationReminder()` to include SMS
3. Add phone number validation (Philippine format: 09XXXXXXXXX)

 Step 3: Create SMS Components
1. `SemaphoreSmsChannel` class
2. SMS message formatting classes
3. SMS logging service (optional)

 Step 4: Testing
1. Test with sample prenatal appointments
2. Test with sample immunization schedules
3. Verify delivery and logging

 Semaphore SMS Features

 Message Types
- Regular SMS: 1 credit per message
- Priority SMS: 2 credits per message (for urgent reminders)
- Bulk SMS: Multiple recipients

 Rate Limits
- Regular messages: 120 calls per minute
- Account info: 2 calls per minute

 Phone Number Format
- Philippine numbers only
- Format: 09XXXXXXXXX or +639XXXXXXXXX

 Cost Estimation
- Each SMS ≈ ₱1.00 (1 credit)
- Priority SMS ≈ ₱2.00 (2 credits)
- Estimate based on patient volume and reminder frequency

 Security Considerations
- Validate Philippine phone numbers
- Secure API key storage
- Patient data privacy in SMS content
- Opt-out mechanism for patients

 Next Steps
1. ✅ Get Semaphore API credentials
2. ✅ Purchase initial SMS credits
3. ✅ Install and configure package
4. ✅ Implement SMS channels
5. ✅ Test integration
6. ✅ Deploy to production

 Files to Modify/Create
- `NotificationService.php` - Add SMS functionality
- `HealthcareNotification.php` - Add SMS channel support
- `SemaphoreSmsChannel.php` - New SMS channel class
- Migration for `sms_logs` table (optional)
- Configuration in `config/services.php`

---
This integration will enhance your existing notification system by adding SMS capabilities while maintaining current in-app notifications.