<?php

namespace Database\Factories;

use App\Models\Workout;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workout>
 */
class WorkoutFactory extends Factory
{
    public function definition(): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return [
            'program_id' => Program::factory(),
            'title' => fake()->randomElement(['Upper Body', 'Lower Body', 'Full Body', 'Cardio', 'Core']) . ' - ' . fake()->randomElement($days),
            'description' => fake()->optional()->sentence(),
            'day_number' => fake()->numberBetween(1, 7),
            'duration_minutes' => fake()->numberBetween(30, 90),
        ];
    }
}
