<?php

namespace Database\Factories;

use App\Models\UserMembership;
use App\Models\User;
use App\Models\MembershipPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserMembership>
 */
class UserMembershipFactory extends Factory
{
    public function definition(): array
    {
        $startDate = now();
        $durationDays = fake()->randomElement([30, 60, 90, 180]);
        $endDate = $startDate->copy()->addDays($durationDays);

        return [
            'user_id' => User::factory(),
            'membership_plan_id' => MembershipPlan::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'remaining_sessions' => fake()->numberBetween(0, 24),
            'remaining_days' => fake()->numberBetween(0, $durationDays),
            'total_days' => $durationDays,
            'status' => fake()->randomElement(['active', 'expired', 'cancelled']),
            'last_check_in' => fake()->optional()->dateTimeThisYear(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
        ]);
    }
}
