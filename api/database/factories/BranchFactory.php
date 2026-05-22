<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company() . ' ' . fake()->randomElement(['Fitness', 'Gym', 'Center', 'Club']);

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . time() . '-' . rand(1, 999),
            'icon' => fake()->randomElement(['dumbbell', 'running', 'heartbeat', 'fire', 'lightning-bolt']),
            'description' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 100),
            'is_active' => true,
            'image' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
