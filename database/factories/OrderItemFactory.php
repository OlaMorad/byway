<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $course = Course::inRandomOrder()->first() ?? Course::factory();
        return [
            'order_id' => Order::inRandomOrder()->first()?->id ?? Order::factory(),
            'course_id' => $course->id,
            'price' => $course->price ?? $this->faker->randomFloat(2, 50, 1000),
        ];
    }
}
