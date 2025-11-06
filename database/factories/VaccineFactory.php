<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vaccine>
 */
class VaccineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['BCG', 'Hepatitis B', 'DPT', 'OPV', 'Measles', 'MMR']),
            'category' => fake()->randomElement(['infant', 'child']),
            'dosage' => fake()->randomElement(['0.5ml', '1ml', '0.1ml']),
            'dose_count' => fake()->numberBetween(1, 3),
            'current_stock' => fake()->numberBetween(10, 100),
            'min_stock' => 10,
            'expiry_date' => fake()->dateTimeBetween('+6 months', '+2 years'),
            'storage_temp' => fake()->randomElement(['2-8°C', '-20°C']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
