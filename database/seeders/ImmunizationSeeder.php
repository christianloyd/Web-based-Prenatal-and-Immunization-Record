<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImmunizationSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        DB::table('immunizations')->delete();

        // Get all child records
        $children = DB::table('child_records')->get()->toArray();

        if (empty($children)) {
            $this->command->error('No child records found! Please run ChildRecordSeeder first.');
            return;
        }

        // Get all vaccines
        $vaccines = DB::table('vaccines')->get()->toArray();

        if (empty($vaccines)) {
            $this->command->error('No vaccines found! Please run VaccineSeeder first.');
            return;
        }

        // Define vaccine schedule (age in months when vaccine should be given)
        $vaccineSchedule = [
            'BCG' => [0], // At birth
            'Pentavalent' => [1.5, 2.5, 3.5], // 6, 10, 14 weeks
            'OPV' => [1.5, 2.5], // 6, 10 weeks
            'IPV' => [3.5, 9], // 14 weeks, 9 months
            'PCV' => [1.5, 2.5, 3.5], // 6, 10, 14 weeks
            'MCV' => [9, 12], // 9 months, 12 months
            'Vitamin A' => [6, 12] // 6 months, 12 months
        ];

        $immunizations = [];
        $immunizationCounter = 1;

        foreach ($children as $child) {
            $childBirthDate = Carbon::parse($child->birthdate);
            $childAgeInMonths = $childBirthDate->diffInMonths(Carbon::now());

            // Determine vaccination status for this child
            // 30% fully vaccinated, 70% partially vaccinated
            $isFullyVaccinated = rand(1, 100) <= 30;
            
            $availableVaccines = array_keys($vaccineSchedule);
            
            if ($isFullyVaccinated) {
                // Fully vaccinated child - all age-appropriate vaccines completed
                $completedVaccines = $availableVaccines;
            } else {
                // Partially vaccinated - 3-5 vaccines completed
                $completedVaccineCount = rand(3, 5);
                $completedVaccines = array_slice($availableVaccines, 0, $completedVaccineCount);
            }

            foreach ($vaccines as $vaccine) {
                $vaccineName = $vaccine->name;
                
                if (!isset($vaccineSchedule[$vaccineName])) {
                    continue; // Skip if not in our schedule
                }

                $scheduleAges = $vaccineSchedule[$vaccineName];
                $isVaccineCompleted = in_array($vaccineName, $completedVaccines);

                foreach ($scheduleAges as $doseIndex => $scheduleAgeMonths) {
                    $doseNumber = $doseIndex + 1;
                    $scheduledDate = $childBirthDate->copy()->addMonths($scheduleAgeMonths);
                    
                    // Determine status based on child age and completion status
                    $status = $this->determineImmunizationStatus(
                        $childAgeInMonths, 
                        $scheduleAgeMonths, 
                        $isVaccineCompleted,
                        $doseIndex,
                        count($scheduleAges),
                        $isFullyVaccinated
                    );

                    // Calculate next due date for multi-dose vaccines
                    $nextDueDate = null;
                    if ($doseIndex < count($scheduleAges) - 1 && $status === 'Done') {
                        $nextScheduleAge = $scheduleAges[$doseIndex + 1];
                        $nextDueDate = $childBirthDate->copy()->addMonths($nextScheduleAge)->format('Y-m-d');
                    }

                    // Generate realistic notes based on status
                    $notes = $this->generateNotes($status, $vaccineName, $doseNumber);

                    $immunizations[] = [
                        'formatted_immunization_id' => 'IMM' . str_pad($immunizationCounter++, 4, '0', STR_PAD_LEFT),
                        'child_record_id' => $child->id,
                        'vaccine_id' => $vaccine->id,
                        'vaccine_name' => $vaccineName,
                        'dose' => $this->getDoseDescription($vaccineName, $doseNumber, count($scheduleAges)),
                        'schedule_date' => $scheduledDate->format('Y-m-d'),
                        'schedule_time' => $this->generateScheduleTime(),
                        'status' => $status,
                        'notes' => $notes,
                        'next_due_date' => $nextDueDate,
                        'created_at' => Carbon::now()->subDays(rand(1, 180)),
                        'updated_at' => Carbon::now()->subDays(rand(1, 30))
                    ];
                }
            }
        }

        // Insert immunizations
        DB::table('immunizations')->insert($immunizations);

        $completedCount = collect($immunizations)->where('status', 'Done')->count();
        $upcomingCount = collect($immunizations)->where('status', 'Upcoming')->count();

        // Count fully vs partially vaccinated children
        $fullyVaccinatedCount = 0;
        $partiallyVaccinatedCount = 0;
        
        foreach ($children as $child) {
            $childImmunizations = collect($immunizations)->where('child_record_id', $child->id);
            $completedForChild = $childImmunizations->where('status', 'Done')->count();
            $totalScheduledForChild = $childImmunizations->count();
            
            if ($completedForChild === $totalScheduledForChild && $totalScheduledForChild > 0) {
                $fullyVaccinatedCount++;
            } else {
                $partiallyVaccinatedCount++;
            }
        }

        $this->command->info('Immunization seeder completed successfully!');
        $this->command->info('Generated: ' . count($immunizations) . ' immunization records for ' . count($children) . ' children');
        $this->command->info("Status breakdown: {$completedCount} Done, {$upcomingCount} Upcoming");
        $this->command->info("Children vaccination status: {$fullyVaccinatedCount} fully vaccinated, {$partiallyVaccinatedCount} partially vaccinated");
        $this->command->info('No missed immunizations - only Done and Upcoming statuses');
    }

    private function determineImmunizationStatus(
        int $childAgeMonths, 
        float $scheduleAgeMonths, 
        bool $isVaccineCompleted,
        int $doseIndex,
        int $totalDoses,
        bool $isFullyVaccinated
    ): string {
        // If child hasn't reached scheduled age yet
        if ($childAgeMonths < $scheduleAgeMonths) {
            return 'Upcoming';
        }

        // If child has passed scheduled age
        if ($childAgeMonths >= $scheduleAgeMonths) {
            // For fully vaccinated children, all age-appropriate vaccines are done
            if ($isFullyVaccinated) {
                return 'Done';
            }
            
            // For partially vaccinated children
            if ($isVaccineCompleted) {
                // Complete doses sequentially (earlier doses first)
                $completedDoses = ceil($totalDoses * 0.8); // Complete 80% of doses for this vaccine
                if ($doseIndex < $completedDoses) {
                    return 'Done';
                } else {
                    return 'Upcoming'; // Remaining doses are still upcoming
                }
            } else {
                // Vaccine not in completed list for this child
                return 'Upcoming';
            }
        }

        return 'Upcoming';
    }

    private function getDoseDescription(string $vaccineName, int $doseNumber, int $totalDoses): string
    {
        if ($totalDoses === 1) {
            return 'Single dose';
        }

        $ordinals = ['', '1st', '2nd', '3rd', '4th', '5th'];
        return $ordinals[$doseNumber] . ' dose';
    }

    private function generateScheduleTime(): string
    {
        $hours = [8, 9, 10, 11, 13, 14, 15, 16]; // Clinic hours
        $minutes = [0, 30]; // 30-minute intervals
        
        return sprintf('%02d:%02d:00', 
            $hours[array_rand($hours)], 
            $minutes[array_rand($minutes)]
        );
    }

    private function generateNotes(string $status, string $vaccine, int $dose): string
    {
        $notes = [
            'Done' => [
                "Successfully administered {$vaccine} dose {$dose}. No adverse reactions observed.",
                "Vaccination completed. Child tolerated {$vaccine} well.",
                "Dose {$dose} of {$vaccine} given as scheduled. Parent advised on next visit.",
                "Immunization completed without complications.",
                "Child received {$vaccine} vaccination. Normal post-vaccination monitoring.",
                "Vaccine administered successfully. Child's immunization record updated.",
                "Completed {$vaccine} vaccination. Parent provided with vaccination card update."
            ],
            'Upcoming' => [
                "Scheduled for {$vaccine} dose {$dose}. Reminder sent to parent.",
                "Appointment scheduled. Parent confirmed availability.",
                "Next vaccination due. SMS reminder sent.",
                "Upcoming {$vaccine} immunization scheduled.",
                "Dose {$dose} scheduled for next visit.",
                "Vaccination appointment set. Parent notified of schedule.",
                "Next immunization due. Follow-up appointment scheduled."
            ]
        ];

        $statusNotes = $notes[$status] ?? ['No notes available.'];
        return $statusNotes[array_rand($statusNotes)];
    }
}