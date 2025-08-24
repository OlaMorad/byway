<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
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
            'total_amount' => $this->faker->randomFloat(2, 50, 2000),
            'payment_id' => Payment::inRandomOrder()->first()?->id ?? Payment::factory(),
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled']),
        ];
    }
}
