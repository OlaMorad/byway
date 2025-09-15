<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::where('role', 'instructor')->inRandomOrder()->first()->id,
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'image_url' => $this->faker->url,
            'video_url' => $this->faker->url,
            'duration' => 0,
            //'duration' => $this->faker->numberBetween(30, 180), // مدة الكورس بالدقائق
            //'duration' => $this->faker->numberBetween(0, 3) . ':' . $this->faker->numberBetween(0, 59),
            'status' => $this->faker->randomElement(['published', 'pending']),
            'price' => $this->faker->randomFloat(2, 0, 1000),
            'category_id' =>  Category::inRandomOrder()->first()->id,
        ];
    }
}
