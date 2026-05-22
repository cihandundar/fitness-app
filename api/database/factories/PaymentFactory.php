<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'user_membership_id' => UserMembership::factory(),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'status' => fake()->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'payment_method' => fake()->randomElement(['credit_card', 'bank_transfer', 'cash', 'iyzico', 'stripe']),
            'transaction_id' => fake()->optional()->uuid(),
            'payment_details' => fake()->optional()->randomElements(['gateway' => 'iyzico', 'installment' => fake()->numberBetween(1, 12)]),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }
}
