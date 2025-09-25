<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PrenatalCheckup;
use Carbon\Carbon;

echo "=== Testing Missed Checkup Functionality ===\n";
echo "Today's date: " . Carbon::today() . "\n\n";

// Check our test data
$checkups = PrenatalCheckup::whereIn('id', [196, 197])->get();

foreach ($checkups as $checkup) {
    echo "ID: " . $checkup->id . "\n";
    echo "Formatted ID: " . $checkup->formatted_checkup_id . "\n";
    echo "Status: " . $checkup->status . "\n";
    echo "Checkup Date: " . $checkup->checkup_date . "\n";
    echo "Is Today: " . ($checkup->checkup_date->isToday() ? 'YES' : 'NO') . "\n";
    echo "Should show Mark as Missed button: " .
         ($checkup->status === 'scheduled' && $checkup->checkup_date->isToday() ? 'YES' : 'NO') . "\n";
    echo "Should show Reschedule button: " .
         ($checkup->status === 'missed' ? 'YES' : 'NO') . "\n";
    echo "---\n";
}