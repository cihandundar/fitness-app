<?php

namespace Database\Factories;

use App\Models\TrainerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainerProfile>
 */
class TrainerProfileFactory extends Factory
{
    public function definition(): array
    {
        $specializations = [
            'Strength Training', 'Weight Loss', 'Bodybuilding', 'CrossFit',
            'Yoga', 'Pilates', 'HIIT', 'Rehabilitation', 'Sports Performance'
        ];

        return [
            'user_id' => User::factory()->trainer(),
            'specialization' => fake()->randomElements($specializations, fake()->numberBetween(1, 3)),
            'experience_years' => fake()->numberBetween(1, 20),
            'certifications' => fake()->randomElements(['ACE', 'NASM', 'NSCA', 'ACSM', 'ISSA'], fake()->numberBetween(1, 3)),
            'bio' => fake()->paragraph(),
            'hourly_rate' => fake()->randomFloat(2, 100, 500),
            'is_available' => fake()->boolean(80),
        ];
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => true,
        ]);
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
