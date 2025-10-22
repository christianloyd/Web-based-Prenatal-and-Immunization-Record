<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\SmsService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Test SMS sending command
 */
Artisan::command('sms:test {phone} {message?}', function ($phone, $message = null) {
    $smsService = app(SmsService::class);

    $defaultMessage = $message ?? "Hello! This is a test SMS from HealthCare System. Your SMS integration is working!";

    $this->info("Sending test SMS to: {$phone}");
    $this->info("Message: {$defaultMessage}");

    $result = $smsService->sendSms($phone, $defaultMessage);

    if ($result['success']) {
        $this->info("✓ SMS sent successfully!");
        $this->line(json_encode($result['data'] ?? [], JSON_PRETTY_PRINT));
    } else {
        $this->error("✗ Failed to send SMS");
        $this->error($result['message']);
    }
})->purpose('Test SMS sending functionality');

/**
 * Test appointment reminder SMS
 */
Artisan::command('sms:test-appointment', function () {
    $smsService = app(SmsService::class);

    $testPhone = '09497428155'; // Your phone number
    $testName = 'Christian';
    $testDate = now()->addDay()->format('F d, Y');

    $result = $smsService->sendAppointmentReminder(
        $testPhone,
        $testName,
        $testDate,
        'prenatal checkup'
    );

    if ($result['success']) {
        $this->info("✓ Appointment reminder SMS sent successfully!");
    } else {
        $this->error("✗ Failed to send appointment reminder");
        $this->error($result['message']);
    }
})->purpose('Test appointment reminder SMS');

/**
 * Test full notification system with real data
 */
Artisan::command('sms:test-full-system', function () {
    $this->info("========================================");
    $this->info("Testing Full SMS Notification System");
    $this->info("========================================\n");

    // Step 1: Check for patients in database
    $this->info("Step 1: Checking database for patients...");
    $patientsCount = \App\Models\Patient::count();
    $this->line("Found {$patientsCount} patient(s) in database");

    if ($patientsCount > 0) {
        $patientWithContact = \App\Models\Patient::whereNotNull('contact')->first();
        if ($patientWithContact) {
            $this->info("✓ Found patient with contact: {$patientWithContact->full_name} ({$patientWithContact->contact})");
        } else {
            $this->warn("⚠ No patients have contact numbers set");
        }
    }

    // Step 2: Check for tomorrow's appointments
    $this->info("\nStep 2: Checking for appointments tomorrow...");
    $tomorrow = \Carbon\Carbon::tomorrow()->toDateString();
    $this->line("Tomorrow's date: {$tomorrow}");

    $upcomingCheckups = \App\Models\PrenatalCheckup::where('checkup_date', $tomorrow)
        ->where('status', '!=', 'Completed')
        ->with(['prenatalRecord.patient'])
        ->get();

    $this->line("Found {$upcomingCheckups->count()} appointment(s) for tomorrow");

    if ($upcomingCheckups->count() > 0) {
        foreach ($upcomingCheckups as $checkup) {
            $patient = $checkup->prenatalRecord->patient ?? null;
            $patientName = $patient ? $patient->full_name : 'Unknown';
            $contact = $patient ? $patient->contact : 'No contact';
            $this->line("  - Patient: {$patientName} | Contact: {$contact}");
        }
    } else {
        $this->warn("⚠ No appointments scheduled for tomorrow");
        $this->line("   Create a prenatal checkup for tomorrow to test SMS");
    }

    // Step 3: Run notification check
    $this->info("\nStep 3: Running notification check...");
    \App\Services\NotificationService::checkUpcomingAppointments();

    if ($upcomingCheckups->count() > 0) {
        $this->info("✓ SMS reminders have been sent to patients with appointments tomorrow!");
        $this->line("   Check your phone if you're scheduled for tomorrow");
    } else {
        $this->info("ℹ No SMS sent (no appointments for tomorrow)");
    }

    // Step 4: Test vaccination reminders
    $this->info("\nStep 4: Checking for vaccination reminders...");
    $childrenCount = \App\Models\ChildRecord::count();
    $this->line("Found {$childrenCount} child record(s) in database");

    if ($childrenCount > 0) {
        \App\Services\NotificationService::checkVaccinationsDue();
        $this->info("✓ Vaccination check completed");
    }

    // Step 5: Summary
    $this->info("\n========================================");
    $this->info("Test Summary:");
    $this->info("========================================");
    $this->line("Patients in DB: {$patientsCount}");
    $this->line("Appointments tomorrow: {$upcomingCheckups->count()}");
    $this->line("Children in DB: {$childrenCount}");
    $this->info("\nCheck storage/logs/laravel.log for detailed SMS logs");

})->purpose('Test full SMS notification system with database data');

/**
 * Create test appointment for SMS testing
 */
Artisan::command('sms:create-test-appointment {phone?}', function ($phone = null) {
    $this->info("Creating test appointment for SMS testing...\n");

    $testPhone = $phone ?? '09497428155';

    // Check if patient with this number exists
    $patient = \App\Models\Patient::where('contact', $testPhone)->first();

    if (!$patient) {
        $this->warn("No patient found with contact: {$testPhone}");
        $this->info("Creating test patient...");

        $patient = \App\Models\Patient::create([
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'name' => 'Test Patient',
            'age' => 25,
            'date_of_birth' => now()->subYears(25),
            'contact' => $testPhone,
            'address' => 'Test Address',
            'occupation' => 'Test'
        ]);

        $this->info("✓ Test patient created: {$patient->full_name}");
    } else {
        $this->info("✓ Using existing patient: {$patient->full_name}");
    }

    // Create prenatal record if doesn't exist
    $prenatalRecord = \App\Models\PrenatalRecord::where('patient_id', $patient->id)->first();

    if (!$prenatalRecord) {
        $lmp = now()->subMonths(2);
        $prenatalRecord = \App\Models\PrenatalRecord::create([
            'patient_id' => $patient->id,
            'last_menstrual_period' => $lmp,
            'lmp' => $lmp,
            'edd' => $lmp->copy()->addMonths(9),
            'expected_delivery_date' => $lmp->copy()->addMonths(9),
            'gravida' => 1,
            'para' => 0,
            'abortion' => 0,
            'living_children' => 0,
            'blood_type' => 'O+',
            'status' => 'Active'
        ]);
        $this->info("✓ Prenatal record created");
    }

    // Create appointment for tomorrow
    $tomorrow = \Carbon\Carbon::tomorrow();

    $checkup = \App\Models\PrenatalCheckup::create([
        'prenatal_record_id' => $prenatalRecord->id,
        'checkup_date' => $tomorrow,
        'next_visit_date' => $tomorrow->copy()->addMonth(),
        'weight' => 60,
        'blood_pressure' => '120/80',
        'status' => 'Scheduled',
        'remarks' => 'Test appointment for SMS'
    ]);

    $this->info("✓ Appointment created for tomorrow ({$tomorrow->format('F d, Y')})");
    $this->info("\n========================================");
    $this->info("Test Data Created Successfully!");
    $this->info("========================================");
    $this->line("Patient: {$patient->full_name}");
    $this->line("Contact: {$patient->contact}");
    $this->line("Appointment: {$tomorrow->format('F d, Y')}");
    $this->info("\nNow run: php artisan sms:test-full-system");
    $this->info("Or wait for scheduled task at 8:00 AM or 2:00 PM");

})->purpose('Create test appointment data for SMS testing');

/**
 * Check how many SMS will be sent tomorrow at 8 AM
 */
Artisan::command('sms:check-tomorrow', function () {
    $tomorrow = \Carbon\Carbon::tomorrow()->toDateString();

    $this->info("========================================");
    $this->info("SMS Reminder Check for Tomorrow");
    $this->info("========================================\n");

    $this->line("Tomorrow's date: {$tomorrow}");

    // Get appointments for tomorrow
    $checkups = \App\Models\PrenatalCheckup::where('checkup_date', $tomorrow)
        ->where('status', '!=', 'Completed')
        ->with(['prenatalRecord.patient'])
        ->get();

    $this->info("\nTotal appointments: {$checkups->count()}");

    if ($checkups->count() > 0) {
        $this->info("\nSMS will be sent to these patients at 8:00 AM:");
        $this->line("--------------------------------------------");

        foreach ($checkups as $checkup) {
            $patient = $checkup->prenatalRecord->patient ?? null;
            $patientName = $patient ? $patient->full_name : 'Unknown';
            $contact = $patient ? ($patient->contact ?: 'NO CONTACT') : 'NO CONTACT';

            $willSendSms = $patient && !empty($patient->contact);
            $status = $willSendSms ? '✓ SMS Will Send' : '✗ No SMS (no contact)';

            $this->line("Patient: {$patientName}");
            $this->line("Contact: {$contact}");
            $this->line("Status: {$status}");
            $this->line("--------------------------------------------");
        }

        $patientsWithContact = $checkups->filter(function($c) {
            $patient = $c->prenatalRecord->patient ?? null;
            return $patient && !empty($patient->contact);
        })->count();

        $this->info("\n📱 Total SMS that will be sent: {$patientsWithContact}");
        $this->info("💰 Cost: ₱{$patientsWithContact} (₱1 per SMS)");
    } else {
        $this->warn("\n⚠️  No appointments scheduled for tomorrow");
        $this->line("No SMS will be sent at 8:00 AM tomorrow.");
    }

    $this->info("\n========================================");

})->purpose('Check how many SMS reminders will be sent tomorrow at 8 AM');

/**
 * Send test SMS to verify immediate sending works
 */
Artisan::command('sms:test-immediate {phone?}', function ($phone = null) {
    $testPhone = $phone ?? '09497428155';

    $this->info("Testing IMMEDIATE SMS sending...\n");

    $smsService = app(\App\Services\SmsService::class);

    // Test 1: Simple SMS
    $this->info("Test 1: Sending simple test SMS...");
    $result1 = $smsService->sendSms($testPhone, "Test: This is an IMMEDIATE SMS from HealthCare System sent at " . now()->format('h:i A'));

    if ($result1['success']) {
        $this->info("✓ Simple SMS sent successfully!");
    } else {
        $this->error("✗ Failed: " . $result1['message']);
    }

    // Test 2: Appointment confirmation
    $this->info("\nTest 2: Sending appointment confirmation SMS...");
    $result2 = $smsService->sendAppointmentReminder(
        $testPhone,
        'Christian',
        now()->addWeek()->format('F d, Y'),
        'prenatal checkup'
    );

    if ($result2['success']) {
        $this->info("✓ Appointment SMS sent successfully!");
    } else {
        $this->error("✗ Failed: " . $result2['message']);
    }

    $this->info("\n========================================");
    $this->info("Check your phone: {$testPhone}");
    $this->info("You should receive 2 SMS messages");
    $this->info("========================================");

})->purpose('Test immediate SMS sending');
