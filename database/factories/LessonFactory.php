<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'video_url' => fake()->url(),
            'description' => $this->faker->paragraph,
            'video_duration' => $this->faker->numberBetween(60,3600),
            'course_id' => Course::inRandomOrder()->first()->id,
        ];
    }
}
