<?php

namespace Database\Factories;

use App\Models\CompletedWorkout;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompletedWorkout>
 */
class CompletedWorkoutFactory extends Factory
{
    protected $model = CompletedWorkout::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'workout_id' => Workout::factory(),
            'started_at' => fake()->dateTimeThisYear(),
            'completed_at' => fake()->optional(0.8)->dateTimeBetween($this->getStartTime(), '+3 hours'),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => fake()->dateTimeBetween($attributes['started_at'], '+3 hours'),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => null,
        ]);
    }

    private function getStartTime(): string
    {
        return '-3 hours';
    }
}
