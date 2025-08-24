<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::where('role', '!=', 'admin')->inRandomOrder()->first()?->id ?? User::factory(),
            'stripe_payment_intent_id' => $this->faker->uuid(),
            'status' => $this->faker->randomElement(['pending', 'succeeded', 'failed']),
            'amount' => $this->faker->randomFloat(2, 0, 10000), // بين 0 و 10,000 فقط
            'currency' => 'usd',
            'type' => $this->faker->randomElement(['payment', 'withdrawal']),
            'response_payload' => [
                'payment_method' => $this->faker->randomElement(['bank', 'paypal', 'stripe']),
                'account_name'   => $this->faker->name(),
                'account_number' => $this->faker->bankAccountNumber(),
                'bank_name'      => $this->faker->company(),
                'amount'         => $this->faker->randomFloat(2, 1, 10000),
                'email'          => $this->faker->safeEmail(),
            ],
            'created_at' => $this->faker->dateTimeBetween('2022-01-01', '2025-12-31'),
            'updated_at' => now(),
        ];
    }
}
