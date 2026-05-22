<?php

namespace Database\Factories;

use App\Models\EquipmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EquipmentType>
 */
class EquipmentTypeFactory extends Factory
{
    public function definition(): array
    {
        $icons = ['🏋️', '🏃', '🚴', '🏊', '🤸', '🧘', '🏸', '🎾', '⛹️', '🥊'];
        $name = fake()->randomElement(['Dumbbell', 'Barbell', 'Kettlebell', 'Treadmill', 'Bike', 'Elliptical', 'Cable', 'Smith Machine', 'Leg Press', 'Pull-up Bar']);

        return [
            'name' => $name,
            'slug' => fake()->unique()->slug(),
            'icon' => fake()->randomElement($icons),
            'sort_order' => fake()->numberBetween(1, 50),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
