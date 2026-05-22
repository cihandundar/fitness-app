<?php

namespace Database\Factories;

use App\Models\MembershipPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipPlan>
 */
class MembershipPlanFactory extends Factory
{
    public function definition(): array
    {
        $types = ['gym', 'pt', 'hybrid'];

        $name = fake()->randomElement(['Basic', 'Pro', 'Premium', 'Elite']) . ' ' . fake()->randomElement(['Plan', 'Package', 'Membership']);

        return [
            'name' => $name,
            'slug' => fake()->unique()->slug(3) . '-' . time() . '-' . rand(1, 999),
            'price' => fake()->randomFloat(2, 100, 5000),
            'type' => fake()->randomElement($types),
            'description' => fake()->paragraph(),
            'features' => fake()->randomElements(['Unlimited Access', 'PT Sessions', 'Locker', 'Sauna', 'Massage', 'Nutrition Plan'], fake()->numberBetween(2, 5)),
            'is_featured' => fake()->boolean(30),
            'is_active' => true,
            'duration_days' => fake()->randomElement([30, 60, 90, 180, 365]),
            'session_count' => fake()->randomElement([0, 4, 8, 12, 24]),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
