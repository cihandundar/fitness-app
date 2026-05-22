<?php

namespace Database\Factories;

use App\Models\CompletedWorkout;
use App\Models\Exercise;
use App\Models\ExerciseLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExerciseLog>
 */
class ExerciseLogFactory extends Factory
{
    protected $model = ExerciseLog::class;

    public function definition(): array
    {
        return [
            'completed_workout_id' => CompletedWorkout::factory(),
            'exercise_id' => Exercise::factory(),
            'set_number' => fake()->numberBetween(1, 5),
            'weight' => fake()->optional(0.7)->randomFloat(1, 5, 200),
            'reps' => fake()->optional(0.7)->numberBetween(1, 20),
            'rest_time' => fake()->optional(0.5)->numberBetween(30, 180),
            'is_completed' => true,
        ];
    }

    public function forCompletedWorkout(CompletedWorkout $workout): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_workout_id' => $workout->id,
        ]);
    }

    public function forExercise(Exercise $exercise): static
    {
        return $this->state(fn (array $attributes) => [
            'exercise_id' => $exercise->id,
        ]);
    }
}
