<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only proceed if appointments table is empty to avoid duplicates
        if (DB::table('appointments')->count() > 0) {
            return; // Skip if appointments already exist
        }

        // Transfer existing prenatal checkup data to appointments table
        $checkups = DB::table('prenatal_checkups')->whereNull('appointment_id')->get();

        foreach ($checkups as $checkup) {
            // Create appointment for each existing checkup
            $appointmentId = DB::table('appointments')->insertGetId([
                'formatted_appointment_id' => 'APT' . str_pad($checkup->id, 4, '0', STR_PAD_LEFT),
                'patient_id' => $checkup->patient_id,
                'prenatal_record_id' => $checkup->prenatal_record_id ?? null,
                'appointment_date' => $checkup->checkup_date,
                'appointment_time' => $checkup->checkup_time ?? '09:00:00',
                'type' => 'prenatal_checkup',
                'status' => $this->mapCheckupStatusToAppointmentStatus($checkup->status),
                'conducted_by' => $checkup->conducted_by ?? null,
                'notes' => $checkup->notes,
                'created_at' => $checkup->created_at,
                'updated_at' => $checkup->updated_at,
            ]);

            // Update the checkup record to link to the new appointment
            DB::table('prenatal_checkups')
                ->where('id', $checkup->id)
                ->update([
                    'appointment_id' => $appointmentId,
                    'status' => $this->mapCheckupStatusToMedicalStatus($checkup->status)
                ]);

            // Create follow-up appointment if next_visit_date exists
            if ($checkup->next_visit_date) {
                DB::table('appointments')->insert([
                    'formatted_appointment_id' => 'APT' . str_pad($checkup->id + 100000, 4, '0', STR_PAD_LEFT), // Offset to avoid duplicates
                    'patient_id' => $checkup->patient_id,
                    'prenatal_record_id' => $checkup->prenatal_record_id ?? null,
                    'appointment_date' => $checkup->next_visit_date,
                    'appointment_time' => $checkup->next_visit_time ?? '09:00:00',
                    'type' => 'prenatal_checkup',
                    'status' => 'scheduled',
                    'notes' => $checkup->next_visit_notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove appointment references from prenatal_checkups
        DB::table('prenatal_checkups')->update(['appointment_id' => null]);

        // Delete all appointments
        DB::table('appointments')->truncate();
    }

    private function mapCheckupStatusToAppointmentStatus($checkupStatus)
    {
        return match($checkupStatus) {
            'completed' => 'completed',
            'upcoming', 'scheduled' => 'scheduled',
            'cancelled' => 'cancelled',
            'rescheduled' => 'rescheduled',
            default => 'scheduled'
        };
    }

    private function mapCheckupStatusToMedicalStatus($checkupStatus)
    {
        return match($checkupStatus) {
            'completed' => 'completed',
            'upcoming', 'scheduled', 'cancelled', 'rescheduled' => 'pending',
            default => 'pending'
        };
    }
};
