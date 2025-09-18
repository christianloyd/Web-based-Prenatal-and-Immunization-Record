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

        // Define exact immunization sequence as requested
        $immunizationSequence = [
            ['vaccine' => 'BCG', 'dose' => '1st Dose', 'age_weeks' => 0],
            ['vaccine' => 'Pentavalent', 'dose' => '1st Dose', 'age_weeks' => 6],
            ['vaccine' => 'Pentavalent', 'dose' => '2nd Dose', 'age_weeks' => 10],
            ['vaccine' => 'Pentavalent', 'dose' => '3rd Dose', 'age_weeks' => 14],
            ['vaccine' => 'OPV', 'dose' => '1st Dose', 'age_weeks' => 6],
            ['vaccine' => 'OPV', 'dose' => '2nd Dose', 'age_weeks' => 10],
            ['vaccine' => 'OPV', 'dose' => '3rd Dose', 'age_weeks' => 14],
            ['vaccine' => 'IPV', 'dose' => '1st Dose', 'age_weeks' => 14],
            ['vaccine' => 'IPV', 'dose' => '2nd Dose', 'age_weeks' => 40],
            ['vaccine' => 'PCV', 'dose' => '1st Dose', 'age_weeks' => 6],
            ['vaccine' => 'PCV', 'dose' => '2nd Dose', 'age_weeks' => 10],
            ['vaccine' => 'PCV', 'dose' => '3rd Dose', 'age_weeks' => 14],
            ['vaccine' => 'Measles', 'dose' => '1st Dose', 'age_weeks' => 40], // MCV 1
            ['vaccine' => 'Measles', 'dose' => '2nd Dose', 'age_weeks' => 52], // MCV 2
            ['vaccine' => 'Vitamin A', 'dose' => '1st Dose', 'age_weeks' => 26], // Vitamins
        ];

        $immunizations = [];
        $childIndex = 0;
        $immunizationIdCounter = 1;

        // Sort children by age (oldest first) for easier distribution
        $sortedChildren = $children->sortByDesc(function($child) {
            return Carbon::parse($child->birthdate)->diffInWeeks(Carbon::now());
        });

        foreach ($sortedChildren as $child) {
            $birthDate = Carbon::parse($child->birthdate);
            $currentAgeWeeks = $birthDate->diffInWeeks(Carbon::now());

            $this->command->info("Processing child #{$childIndex}: {$child->child_name} (Age: {$currentAgeWeeks} weeks)");

            // Determine immunization status based on distribution:
            // First 6 children (0-5): Fully immunized (all age-appropriate vaccines Done)
            // Next 3 children (6-8): Partially immunized (some Done, next one Upcoming)
            // Last 1 child (9): Not vaccinated (first vaccine Upcoming)

            if ($childIndex < 6) {
                // Fully immunized - all age-appropriate vaccines are Done
                $this->createFullyImmunizedChild($child, $birthDate, $currentAgeWeeks, $immunizationSequence, $vaccines, $immunizations, $immunizationIdCounter);
            } elseif ($childIndex < 9) {
                // Partially immunized - some Done, next one Upcoming
                $this->createPartiallyImmunizedChild($child, $birthDate, $currentAgeWeeks, $immunizationSequence, $vaccines, $immunizations, $immunizationIdCounter);
            } else {
                // Not vaccinated - first age-appropriate vaccine is Upcoming
                $this->createUnvaccinatedChild($child, $birthDate, $currentAgeWeeks, $immunizationSequence, $vaccines, $immunizations, $immunizationIdCounter);
            }

            $childIndex++;
        }

        // Insert immunizations in batches
        if (!empty($immunizations)) {
            $chunks = array_chunk($immunizations, 50);
            foreach ($chunks as $chunk) {
                DB::table('immunizations')->insert($chunk);
            }
        }

        $this->command->info('✅ Immunization seeder completed successfully!');
        $this->command->info('Generated: ' . count($immunizations) . ' immunization records');
        $this->command->info('🎯 Distribution: 6 fully immunized, 3 partial, 1 unvaccinated');
        $this->command->info('📅 Based on each child\'s actual age and current date');

        // Display statistics
        $statusCounts = collect($immunizations)->countBy('status');
        foreach ($statusCounts as $status => $count) {
            $this->command->info("   {$status}: {$count} records");
        }
    }

    private function createFullyImmunizedChild($child, $birthDate, $currentAgeWeeks, $sequence, $vaccines, &$immunizations, &$idCounter)
    {
        // All age-appropriate vaccines are Done
        foreach ($sequence as $schedule) {
            if ($currentAgeWeeks >= $schedule['age_weeks']) {
                $vaccine = $vaccines->get($schedule['vaccine']);
                if (!$vaccine) continue;

                $immunizationDate = $birthDate->copy()->addWeeks($schedule['age_weeks']);
                $immunizations[] = $this->createImmunizationRecord(
                    $child, $vaccine, $schedule, $immunizationDate, 'Done', $idCounter
                );
                $idCounter++;
            }
        }
    }

    private function createPartiallyImmunizedChild($child, $birthDate, $currentAgeWeeks, $sequence, $vaccines, &$immunizations, &$idCounter)
    {
        // Some Done, next one Upcoming
        $upcomingSet = false;

        foreach ($sequence as $schedule) {
            if ($currentAgeWeeks >= $schedule['age_weeks']) {
                $vaccine = $vaccines->get($schedule['vaccine']);
                if (!$vaccine) continue;

                $immunizationDate = $birthDate->copy()->addWeeks($schedule['age_weeks']);
                $immunizations[] = $this->createImmunizationRecord(
                    $child, $vaccine, $schedule, $immunizationDate, 'Done', $idCounter
                );
                $idCounter++;
            } elseif (!$upcomingSet && $currentAgeWeeks >= ($schedule['age_weeks'] - 2)) {
                // Next due vaccine is Upcoming (within 2 weeks)
                $vaccine = $vaccines->get($schedule['vaccine']);
                if (!$vaccine) continue;

                $immunizationDate = $birthDate->copy()->addWeeks($schedule['age_weeks']);
                $immunizations[] = $this->createImmunizationRecord(
                    $child, $vaccine, $schedule, $immunizationDate, 'Upcoming', $idCounter
                );
                $idCounter++;
                $upcomingSet = true;
            }
        }
    }

    private function createUnvaccinatedChild($child, $birthDate, $currentAgeWeeks, $sequence, $vaccines, &$immunizations, &$idCounter)
    {
        // First age-appropriate vaccine is Upcoming
        foreach ($sequence as $schedule) {
            if ($currentAgeWeeks >= $schedule['age_weeks']) {
                $vaccine = $vaccines->get($schedule['vaccine']);
                if (!$vaccine) continue;

                $immunizationDate = $birthDate->copy()->addWeeks($schedule['age_weeks']);
                $immunizations[] = $this->createImmunizationRecord(
                    $child, $vaccine, $schedule, $immunizationDate, 'Upcoming', $idCounter
                );
                $idCounter++;
                break; // Only create the first one as Upcoming
            }
        }
    }

    private function createImmunizationRecord($child, $vaccine, $schedule, $immunizationDate, $status, $idCounter)
    {
        $scheduleTime = sprintf('%02d:%02d:00', rand(8, 17), rand(0, 59));

        return [
            'formatted_immunization_id' => 'IM-' . str_pad($idCounter, 3, '0', STR_PAD_LEFT),
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