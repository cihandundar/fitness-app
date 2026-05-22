<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\EquipmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    public function definition(): array
    {
        $names = [
            'Bench Press', 'Squat', 'Deadlift', 'Pull-up', 'Push-up', 'Lunge',
            'Shoulder Press', 'Bicep Curl', 'Tricep Dip', 'Plank', 'Crunches',
            'Leg Press', 'Lat Pulldown', 'Cable Row', 'Leg Curl', 'Calf Raise'
        ];

        return [
            'name' => fake()->randomElement($names),
            'slug' => fake()->unique()->slug(),
            'muscle_group' => fake()->randomElement(['chest', 'back', 'shoulders', 'legs', 'arms', 'core', 'full_body']),
            'equipment' => fake()->randomElement(['none', 'dumbbells', 'barbell', 'machine', 'cables', 'kettlebell', 'Resistance bands', 'bodyweight']),
            'muscle_group_id' => MuscleGroup::factory(),
            'equipment_type_id' => EquipmentType::factory(),
            'description' => fake()->optional()->sentence(),
            'difficulty' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
