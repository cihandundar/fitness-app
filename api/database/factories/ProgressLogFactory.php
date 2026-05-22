<?php

namespace Database\Factories;

use App\Models\ProgressLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgressLog>
 */
class ProgressLogFactory extends Factory
{
    protected $model = ProgressLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'weight' => fake()->randomFloat(1, 50, 120),
            'body_fat' => fake()->optional(0.7)->randomFloat(1, 8, 30),
            'notes' => fake()->optional(0.5)->sentence(),
            'logged_at' => fake()->dateTimeThisYear(),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
