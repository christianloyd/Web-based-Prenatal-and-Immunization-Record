<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\ChildRecord;
use App\Models\Immunization;
use App\Models\Vaccine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ComprehensiveImmunizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get children records
        $children = ChildRecord::with('patient')->get()->keyBy('child_name');
        $vaccines = Vaccine::all()->keyBy('name');

        $immunizations = [
            // Baby Girl Mendoza (3 months old) - Recent immunizations
            [
                'child_record_id' => $children['Baby Girl Mendoza']->id,
                'vaccine_id' => $vaccines['BCG']->id ?? null,
                'administered_date' => Carbon::parse('2024-07-23'), // Day after birth
                'administered_by' => 'Nurse Maria Santos',
                'batch_number' => 'BCG2024-07-001',
                'site_administered' => 'Left deltoid',
                'adverse_reactions' => null,
                'notes' => 'Birth dose administered as scheduled. No immediate reactions observed.',
                'next_due_date' => null,
            ],
            [
                'child_record_id' => $children['Baby Girl Mendoza']->id,
                'vaccine_id' => $vaccines['Hepatitis B']->id ?? null,
                'administered_date' => Carbon::parse('2024-07-23'), // Birth dose
                'administered_by' => 'Nurse Maria Santos',
                'batch_number' => 'HEPB2024-07-001',
                'site_administered' => 'Right anterolateral thigh',
                'adverse_reactions' => null,
                'notes' => 'Birth dose of Hepatitis B vaccine. Well tolerated.',
                'next_due_date' => Carbon::parse('2024-09-22'), // 2 months later
            ],
            [
                'child_record_id' => $children['Baby Girl Mendoza']->id,
                'vaccine_id' => $vaccines['Pentavalent']->id ?? null,
                'administered_date' => Carbon::parse('2024-09-22'), // 2 months
                'administered_by' => 'Midwife Ana Rodriguez',
                'batch_number' => 'PENTA2024-09-001',
                'site_administered' => 'Left anterolateral thigh',
                'adverse_reactions' => 'Mild fever for 1 day',
                'notes' => '1st dose Pentavalent vaccine. Mild fever resolved with paracetamol.',
                'next_due_date' => Carbon::parse('2024-11-22'), // Next dose in 2 months
            ],

            // Baby Boy Villanueva (6 months old) - More complete immunization schedule
            [
                'child_record_id' => $children['Baby Boy Villanueva']->id,
                'vaccine_id' => $vaccines['BCG']->id ?? null,
                'administered_date' => Carbon::parse('2024-05-19'),
                'administered_by' => 'Nurse Rosa Cruz',
                'batch_number' => 'BCG2024-05-001',
                'site_administered' => 'Left deltoid',
                'adverse_reactions' => null,
                'notes' => 'Birth dose BCG vaccine administered.',
                'next_due_date' => null,
            ],
            [
                'child_record_id' => $children['Baby Boy Villanueva']->id,
                'vaccine_id' => $vaccines['Hepatitis B']->id ?? null,
                'administered_date' => Carbon::parse('2024-05-19'),
                'administered_by' => 'Nurse Rosa Cruz',
                'batch_number' => 'HEPB2024-05-001',
                'site_administered' => 'Right anterolateral thigh',
                'adverse_reactions' => null,
                'notes' => 'Birth dose Hepatitis B.',
                'next_due_date' => Carbon::parse('2024-07-19'),
            ],
            [
                'child_record_id' => $children['Baby Boy Villanueva']->id,
                'vaccine_id' => $vaccines['Pentavalent']->id ?? null,
                'administered_date' => Carbon::parse('2024-07-19'), // 2 months
                'administered_by' => 'Midwife Ana Rodriguez',
                'batch_number' => 'PENTA2024-07-001',
                'site_administered' => 'Left anterolateral thigh',
                'adverse_reactions' => null,
                'notes' => '1st dose Pentavalent vaccine. No reactions.',
                'next_due_date' => Carbon::parse('2024-09-19'),
            ],
            [
                'child_record_id' => $children['Baby Boy Villanueva']->id,
                'vaccine_id' => $vaccines['OPV']->id ?? null,
                'administered_date' => Carbon::parse('2024-07-19'),
                'administered_by' => 'Midwife Ana Rodriguez',
                'batch_number' => 'OPV2024-07-001',
                'site_administered' => 'Oral',
                'adverse_reactions' => null,
                'notes' => '1st dose OPV administered orally.',
                'next_due_date' => Carbon::parse('2024-09-19'),
            ],
            [
                'child_record_id' => $children['Baby Boy Villanueva']->id,
                'vaccine_id' => $vaccines['Pentavalent']->id ?? null,
                'administered_date' => Carbon::parse('2024-09-19'), // 4 months
                'administered_by' => 'Midwife Ana Rodriguez',
                'batch_number' => 'PENTA2024-09-002',
                'site_administered' => 'Right anterolateral thigh',
                'adverse_reactions' => 'Mild redness at injection site',
                'notes' => '2nd dose Pentavalent vaccine. Mild local reaction.',
                'next_due_date' => Carbon::parse('2024-11-19'),
            ],

            // Twin A (Gloria Ramos) - 10 months old, more complete schedule
            [
                'child_record_id' => $children['Baby Girl Ramos (Twin A)']->id,
                'vaccine_id' => $vaccines['BCG']->id ?? null,
                'administered_date' => Carbon::parse('2024-01-27'), // 1 week after birth (NICU discharge)
                'administered_by' => 'NICU Nurse Patricia Lee',
                'batch_number' => 'BCG2024-01-001',
                'site_administered' => 'Left deltoid',
                'adverse_reactions' => null,
                'notes' => 'Delayed due to NICU stay. Administered upon discharge.',
                'next_due_date' => null,
            ],
            [
                'child_record_id' => $children['Baby Girl Ramos (Twin A)']->id,
                'vaccine_id' => $vaccines['Pentavalent']->id ?? null,
                'administered_date' => Carbon::parse('2024-03-20'), // 2 months
                'administered_by' => 'Midwife Ana Rodriguez',
                'batch_number' => 'PENTA2024-03-001',
                'site_administered' => 'Left anterolateral thigh',
                'adverse_reactions' => null,
                'notes' => '1st dose Pentavalent for Twin A.',
                'next_due_date' => Carbon::parse('2024-05-20'),
            ],
            [
                'child_record_id' => $children['Baby Girl Ramos (Twin A)']->id,
                'vaccine_id' => $vaccines['Pentavalent']->id ?? null,
                'administered_date' => Carbon::parse('2024-05-20'), // 4 months
                'administered_by' => 'Midwife Ana Rodriguez',
                'batch_number' => 'PENTA2024-05-001',
                'site_administered' => 'Right anterolateral thigh',
                'adverse_reactions' => 'Mild fever',
                'notes' => '2nd dose Pentavalent for Twin A. Fever managed at home.',
                'next_due_date' => Carbon::parse('2024-07-20'),
            ],
            [
                'child_record_id' => $children['Baby Girl Ramos (Twin A)']->id,
                'vaccine_id' => $vaccines['Pentavalent']->id ?? null,
                'administered_date' => Carbon::parse('2024-07-20'), // 6 months
                'administered_by' => 'Midwife Ana Rodriguez',
                'batch_number' => 'PENTA2024-07-002',
                'site_administered' => 'Left anterolateral thigh',
                'adverse_reactions' => null,
                'notes' => '3rd dose Pentavalent for Twin A. Series completed.',
                'next_due_date' => null,
            ],

            // Twin B (more complex due to longer NICU stay)
            [
                'child_record_id' => $children['Baby Boy Ramos (Twin B)']->id,
                'vaccine_id' => $vaccines['BCG']->id ?? null,
                'administered_date' => Carbon::parse('2024-02-03'), // 2 weeks after birth
                'administered_by' => 'NICU Nurse Patricia Lee',
                'batch_number' => 'BCG2024-02-001',
                'site_administered' => 'Left deltoid',
                'adverse_reactions' => null,
                'notes' => 'Delayed due to extended NICU stay. Medically stable for vaccination.',
                'next_due_date' => null,
            ],
            [
                'child_record_id' => $children['Baby Boy Ramos (Twin B)']->id,
                'vaccine_id' => $vaccines['Pentavalent']->id ?? null,
                'administered_date' => Carbon::parse('2024-03-20'), // 2 months corrected age
                'administered_by' => 'Midwife Ana Rodriguez',
                'batch_number' => 'PENTA2024-03-002',
                'site_administered' => 'Left anterolateral thigh',
                'adverse_reactions' => null,
                'notes' => '1st dose Pentavalent for Twin B. Catch-up schedule.',
                'next_due_date' => Carbon::parse('2024-05-20'),
            ],

            // Sofia Santos (2 years old) - Complete immunization schedule
            [
                'child_record_id' => $children['Sofia Santos']->id,
                'vaccine_id' => $vaccines['MMR']->id ?? null,
                'administered_date' => Carbon::parse('2023-08-15'), // 12 months
                'administered_by' => 'Midwife Ana Rodriguez',
                'batch_number' => 'MMR2023-08-001',
                'site_administered' => 'Left deltoid',
                'adverse_reactions' => 'Mild rash after 1 week',
                'notes' => '1st dose MMR at 12 months. Mild rash resolved spontaneously.',
                'next_due_date' => Carbon::parse('2024-08-15'), // 2nd dose at 24 months
            ],
            [
                'child_record_id' => $children['Sofia Santos']->id,
                'vaccine_id' => $vaccines['MMR']->id ?? null,
                'administered_date' => Carbon::parse('2024-08-15'), // 24 months
                'administered_by' => 'Midwife Ana Rodriguez',
                'batch_number' => 'MMR2024-08-001',
                'site_administered' => 'Right deltoid',
                'adverse_reactions' => null,
                'notes' => '2nd dose MMR completed. Full immunization series done.',
                'next_due_date' => null,
            ],

            // Miguel Garcia (3 years old) - Age-appropriate vaccines
            [
                'child_record_id' => $children['Miguel Garcia']->id,
                'vaccine_id' => $vaccines['MMR']->id ?? null,
                'administered_date' => Carbon::parse('2022-03-10'), // 12 months
                'administered_by' => 'Midwife Ana Rodriguez',
                'batch_number' => 'MMR2022-03-001',
                'site_administered' => 'Left deltoid',
                'adverse_reactions' => null,
                'notes' => '1st dose MMR at 12 months.',
                'next_due_date' => Carbon::parse('2023-03-10'),
            ],
            [
                'child_record_id' => $children['Miguel Garcia']->id,
                'vaccine_id' => $vaccines['MMR']->id ?? null,
                'administered_date' => Carbon::parse('2023-03-10'), // 24 months
                'administered_by' => 'Midwife Ana Rodriguez',
                'batch_number' => 'MMR2023-03-001',
                'site_administered' => 'Right deltoid',
                'adverse_reactions' => null,
                'notes' => '2nd dose MMR completed.',
                'next_due_date' => null,
            ],
        ];

        foreach ($immunizations as $immunizationData) {
            // Only create if vaccine exists
            if ($immunizationData['vaccine_id']) {
                Immunization::create($immunizationData);
            }
        }
    }
}