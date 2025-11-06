<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ChildRecord;
use App\Models\Vaccine;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Immunization>
 */
class ImmunizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'child_record_id' => ChildRecord::factory(),
            'vaccine_id' => Vaccine::factory(),
            'vaccine_name' => fake()->randomElement(['BCG', 'Hepatitis B', 'DPT', 'OPV', 'Measles']),
            'dose' => fake()->randomElement(['1st Dose', '2nd Dose', '3rd Dose']),
            'schedule_date' => fake()->dateTimeBetween('now', '+3 months'),
            'schedule_time' => fake()->time('H:i'),
            'status' => 'Upcoming',
            'notes' => fake()->optional()->sentence(),
            'next_due_date' => fake()->optional()->dateTimeBetween('+1 month', '+6 months'),
            'rescheduled' => false,
            'rescheduled_to_immunization_id' => null,
        ];
    }
}
