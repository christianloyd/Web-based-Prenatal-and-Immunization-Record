<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Patient;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChildRecord>
 */
class ChildRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement(['male', 'female']),
            'birth_height' => fake()->randomFloat(2, 40, 60),
            'birth_weight' => fake()->randomFloat(3, 2, 5),
            'birthdate' => fake()->dateTimeBetween('-2 years', 'now'),
            'birthplace' => fake()->city(),
            'address' => fake()->address(),
            'father_name' => fake()->name('male'),
            'mother_name' => fake()->name('female'),
            'phone_number' => '09' . fake()->numerify('#########'),
            'mother_id' => Patient::factory(),
        ];
    }
}
