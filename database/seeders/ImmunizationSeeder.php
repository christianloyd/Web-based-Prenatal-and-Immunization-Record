<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ChildRecord;
use App\Models\Vaccine;
use Carbon\Carbon;

class ImmunizationSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing immunizations
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('immunizations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get all child records
        $children = ChildRecord::all();

        if ($children->isEmpty()) {
            $this->command->error('No child records found! Please run ChildRecordSeeder first.');
            return;
        }

        // Get available vaccines
        $vaccines = Vaccine::all()->keyBy('name');

        if ($vaccines->isEmpty()) {
            $this->command->error('No vaccines found! Please run VaccineSeeder first.');
            return;
        }

        // Define STRICT sequential immunization schedule - NO SKIPPING ALLOWED
        // Each vaccine must be completed in order, every 4 weeks
        $immunizationSequence = [
            ['vaccine' => 'BCG', 'dose' => '1st Dose', 'age_weeks' => 0, 'order' => 1],

            // 6 weeks (1.5 months)
            ['vaccine' => 'Pentavalent', 'dose' => '1st Dose', 'age_weeks' => 6, 'order' => 2],
            ['vaccine' => 'OPV', 'dose' => '1st Dose', 'age_weeks' => 6, 'order' => 3],
            ['vaccine' => 'PCV', 'dose' => '1st Dose', 'age_weeks' => 6, 'order' => 4],

            // 10 weeks (2.5 months)
            ['vaccine' => 'Pentavalent', 'dose' => '2nd Dose', 'age_weeks' => 10, 'order' => 5],
            ['vaccine' => 'OPV', 'dose' => '2nd Dose', 'age_weeks' => 10, 'order' => 6],
            ['vaccine' => 'PCV', 'dose' => '2nd Dose', 'age_weeks' => 10, 'order' => 7],

            // 14 weeks (3.5 months)
            ['vaccine' => 'Pentavalent', 'dose' => '3rd Dose', 'age_weeks' => 14, 'order' => 8],
            ['vaccine' => 'PCV', 'dose' => '3rd Dose', 'age_weeks' => 14, 'order' => 9],
            ['vaccine' => 'IPV', 'dose' => '1st Dose', 'age_weeks' => 14, 'order' => 10],

            // 26 weeks (6 months)
            ['vaccine' => 'Vitamin A', 'dose' => '1st Dose', 'age_weeks' => 26, 'order' => 11],

            // 40 weeks (9 months)
            ['vaccine' => 'IPV', 'dose' => '2nd Dose', 'age_weeks' => 40, 'order' => 12],
            ['vaccine' => 'MCV', 'dose' => '1st Dose', 'age_weeks' => 40, 'order' => 13],

            // 52 weeks (12 months)
            ['vaccine' => 'Vitamin A', 'dose' => '2nd Dose', 'age_weeks' => 52, 'order' => 14],
            ['vaccine' => 'MCV', 'dose' => '2nd Dose', 'age_weeks' => 52, 'order' => 15],
        ];

        $immunizations = [];
        $childIndex = 0;
        $immunizationIdCounter = 1;

        // Sort children by age (oldest first) for easier distribution
        $sortedChildren = $children->sortByDesc(function($child) {
            return Carbon::parse($child->birthdate)->diffInWeeks(Carbon::now());
        });

        $totalChildren = $sortedChildren->count();

        foreach ($sortedChildren as $child) {
            $birthDate = Carbon::parse($child->birthdate);
            $currentAgeWeeks = $birthDate->diffInWeeks(Carbon::now());

            $this->command->info("Processing child #{$childIndex}: {$child->full_name} (Age: {$currentAgeWeeks} weeks)");

            // Only create immunizations if child has some age (at least 1 week old)
            if ($currentAgeWeeks >= 1) {
                $this->createStrictSequentialImmunizations($child, $birthDate, $currentAgeWeeks, $immunizationSequence, $vaccines, $immunizations, $immunizationIdCounter, $childIndex, $totalChildren);
            } else {
                $this->command->info("Skipping newborn (< 1 week old): {$child->first_name} {$child->last_name}");
            }

            $childIndex++;
        }

        // Sequential immunization logic ensures only next vaccine is upcoming

        // Insert immunizations in batches
        if (!empty($immunizations)) {
            $chunks = array_chunk($immunizations, 50);
            foreach ($chunks as $chunk) {
                DB::table('immunizations')->insert($chunk);
            }
        }

        $this->command->info('✅ Immunization seeder completed successfully!');
        $this->command->info('Generated: ' . count($immunizations) . ' immunization records');
        $this->command->info('🎯 Distribution: 40% fully immunized, 45% partial, 15% starting');
        $this->command->info('📅 Based on each child\'s actual age and current date');

        // Display statistics
        $statusCounts = collect($immunizations)->countBy('status');
        foreach ($statusCounts as $status => $count) {
            $this->command->info("   {$status}: {$count} records");
        }
    }

    private function createStrictSequentialImmunizations($child, $birthDate, $currentAgeWeeks, $sequence, $vaccines, &$immunizations, &$idCounter, $childIndex, $totalChildren)
    {
        // STRICT RULE: Must complete each vaccine in EXACT ORDER - NO SKIPPING
        // Dates must respect the sequence order

        $percentage = ($childIndex / $totalChildren) * 100;
        $lastImmunizationDate = $birthDate->copy(); // Start from birth date

        // Determine where this child should stop in the sequence
        if ($percentage < 40) {
            // 40% - Complete all age-appropriate vaccines
            $stopAtOrder = 999; // Don't stop, complete all
        } elseif ($percentage < 85) {
            // 45% - Stop at a random point (stuck waiting for next dose)
            $maxOrderForAge = 0;
            foreach ($sequence as $schedule) {
                if ($currentAgeWeeks >= $schedule['age_weeks'] + 2) {
                    $maxOrderForAge = max($maxOrderForAge, $schedule['order']);
                }
            }
            $stopAtOrder = rand(5, max(5, $maxOrderForAge - 1)); // Stop somewhere in middle
        } else {
            // 15% - Just starting (stop early)
            $stopAtOrder = rand(1, 4); // Stop after first few vaccines
        }

        // Process vaccines in STRICT chronological order
        foreach ($sequence as $schedule) {
            $vaccine = $vaccines->get($schedule['vaccine']);
            if (!$vaccine) continue;

            // Check if child is old enough for this vaccine
            if ($currentAgeWeeks >= $schedule['age_weeks'] + 1) {

                if ($schedule['order'] <= $stopAtOrder) {
                    // Mark as Done with proper chronological date
                    $targetDate = $birthDate->copy()->addWeeks($schedule['age_weeks']);

                    // Add small random variation (1-7 days) but maintain order
                    $randomDays = rand(0, 7);
                    $immunizationDate = $targetDate->addDays($randomDays);

                    // Ensure this date is after the last immunization
                    if ($immunizationDate->lte($lastImmunizationDate)) {
                        $immunizationDate = $lastImmunizationDate->copy()->addDays(rand(1, 3));
                    }

                    $immunizations[] = $this->createImmunizationRecord(
                        $child, $vaccine, $schedule, $immunizationDate, 'Done', $idCounter
                    );
                    $idCounter++;
                    $lastImmunizationDate = $immunizationDate->copy();

                } else {
                    // This is the NEXT vaccine needed - mark as Upcoming (future date)
                    $daysFromNow = rand(1, 21);
                    $immunizationDate = Carbon::now()->addDays($daysFromNow);

                    $immunizations[] = $this->createImmunizationRecord(
                        $child, $vaccine, $schedule, $immunizationDate, 'Upcoming', $idCounter
                    );
                    $idCounter++;
                    break; // STOP - cannot proceed until this is done
                }
            } else {
                // Child is not old enough yet - mark as upcoming if approaching age
                if ($currentAgeWeeks >= ($schedule['age_weeks'] - 2)) {
                    $weeksUntilDue = $schedule['age_weeks'] - $currentAgeWeeks;
                    $daysFromNow = max(1, $weeksUntilDue * 7 + rand(1, 7));
                    $immunizationDate = Carbon::now()->addDays($daysFromNow);

                    $immunizations[] = $this->createImmunizationRecord(
                        $child, $vaccine, $schedule, $immunizationDate, 'Upcoming', $idCounter
                    );
                    $idCounter++;
                    break; // STOP - cannot proceed until this is done
                }
            }
        }
    }

    private function createImmunizationRecord($child, $vaccine, $schedule, $immunizationDate, $status, $idCounter)
    {
        $scheduleTime = sprintf('%02d:%02d:00', rand(8, 17), rand(0, 59));

        return [
            'child_record_id' => $child->id,
            'vaccine_id' => $vaccine->id,
            'vaccine_name' => $schedule['vaccine'],
            'dose' => $schedule['dose'],
            'schedule_date' => $immunizationDate->format('Y-m-d'),
            'schedule_time' => $scheduleTime,
            'status' => $status,
            'notes' => $this->generateNotes($schedule['vaccine'], $schedule['dose'], $status),
            'next_due_date' => null,
            'created_at' => $immunizationDate->copy()->subDays(rand(0, 2))->format('Y-m-d H:i:s'),
            'updated_at' => $immunizationDate->copy()->subDays(rand(0, 2))->format('Y-m-d H:i:s')
        ];
    }

    private function generateNotes($vaccine, $dose, $status): string
    {
        $notes = [
            'Done' => [
                "Child received {$vaccine} {$dose} successfully. No adverse reactions.",
                "Vaccination completed as scheduled. Child tolerated well.",
                "Immunization administered. Parent advised on next schedule.",
                "Vaccine given without complications. Child is healthy.",
                "Successful vaccination. No immediate side effects observed."
            ],
            'Upcoming' => [
                "Scheduled for {$vaccine} {$dose} vaccination.",
                "Next immunization appointment. Remind parent.",
                "Important: {$vaccine} {$dose} due.",
                "Follow-up vaccination required.",
                "Scheduled immunization appointment."
            ]
        ];

        $statusNotes = $notes[$status] ?? $notes['Done'];
        return $statusNotes[array_rand($statusNotes)];
    }

}