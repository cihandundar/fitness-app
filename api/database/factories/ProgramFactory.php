<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    public function definition(): array
    {
        $titles = [
            'Beginner Strength', 'Muscle Building', 'Fat Loss', 'HIIT',
            'Yoga Basics', 'Crossfit', 'Bodyweight Training', 'Powerlifting'
        ];

        return [
            'created_by' => \App\Models\User::factory()->admin(),
            'title' => fake()->randomElement($titles) . ' ' . fake()->numberBetween(1, 10),
            'slug' => fake()->unique()->slug(),
            'level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'duration_weeks' => fake()->numberBetween(4, 16),
            'description' => fake()->paragraph(),
            'is_active' => true,
            'is_featured' => fake()->boolean(30),
            'is_custom' => false,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function beginner(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 'beginner',
        ]);
    }

    public function intermediate(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 'intermediate',
        ]);
    }

    public function advanced(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 'advanced',
        ]);
    }
}
