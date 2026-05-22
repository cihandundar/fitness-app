<?php

namespace Database\Factories;

use App\Models\MuscleGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MuscleGroup>
 */
class MuscleGroupFactory extends Factory
{
    public function definition(): array
    {
        $names = ['Göğüs', 'Sırt', 'Omuz', 'Biceps', 'Triceps', 'Ön Kol', 'Arka Kol', 'Quadriceps', 'Hamstring', 'Kalça', 'Karın', 'Baldır'];
        $colors = ['violet', 'emerald', 'red', 'blue', 'orange', 'pink', 'cyan', 'yellow'];

        $name = fake()->randomElement($names);

        return [
            'name' => $name,
            'slug' => fake()->unique()->slug(),
            'color' => fake()->randomElement($colors),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
